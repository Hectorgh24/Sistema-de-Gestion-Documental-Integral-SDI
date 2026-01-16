<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Model: Categoria
 * 
 * Gestiona las categorías (tipos) de documentos.
 * Cada categoría define una estructura de campos dinámicos.
 * 
 * Estados: activa | obsoleta
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class Categoria
{
    protected $db;
    protected const TABLE = 'cat_categorias';
    protected const TABLE_CAMPOS = 'conf_columnas_categoria';

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a base de datos
     */
    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? getDBConnection();
    }

    /**
     * Obtener categoría por ID
     * 
     * @param int $id_categoria ID de la categoría
     * @return array|null Datos de la categoría
     */
    public function obtenerPorId($id_categoria)
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id_categoria = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_categoria]);

        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($categoria) {
            // Obtener campos dinámicos
            $categoria['campos'] = $this->obtenerCampos($id_categoria);
        }

        return $categoria ?: null;
    }

    /**
     * Obtener categoría por nombre
     * 
     * @param string $nombre Nombre de la categoría
     * @return array|null Datos de la categoría
     */
    public function obtenerPorNombre($nombre)
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE nombre_categoria = :nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Listar categorías activas
     * 
     * @param int $limit Límite de registros
     * @param int $offset Desplazamiento
     * @param bool $solo_activas Filtrar solo categorías activas
     * @return array Arreglo de categorías
     */
    public function listar($limit = 10, $offset = 0, $solo_activas = true)
    {
        $sql = "SELECT c.*, COUNT(cc.id_columna) as cantidad_campos,
                       COUNT(DISTINCT rd.id_registro) as cantidad_documentos
                FROM " . self::TABLE . " c
                LEFT JOIN " . self::TABLE_CAMPOS . " cc ON c.id_categoria = cc.id_categoria
                LEFT JOIN registros_documentos rd ON c.id_categoria = rd.id_categoria
                WHERE 1=1";

        $params = [];

        if ($solo_activas) {
            $sql .= " AND c.estado = 'activa'";
        }

        $sql .= " GROUP BY c.id_categoria ORDER BY c.nombre_categoria ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar categorías
     * 
     * @param bool $solo_activas Contar solo activas
     * @return int Total de categorías
     */
    public function contar($solo_activas = true)
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::TABLE . " WHERE 1=1";
        
        if ($solo_activas) {
            $sql .= " AND estado = 'activa'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Crear nueva categoría
     * 
     * @param array $data Datos de la categoría
     *   - nombre_categoria (requerido)
     *   - descripcion (opcional)
     *   - campos (opcional): Array de campos dinámicos a crear
     * 
     * @return int|false ID de la categoría creada
     */
    public function crear($data)
    {
        try {
            if (empty($data['nombre_categoria'])) {
                throw new \Exception("El nombre de la categoría es requerido");
            }

            // Verificar unicidad del nombre
            if ($this->obtenerPorNombre($data['nombre_categoria'])) {
                throw new \Exception("La categoría ya existe");
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO " . self::TABLE . "
                    (nombre_categoria, descripcion, estado)
                    VALUES (:nombre, :descripcion, 'activa')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre'       => trim($data['nombre_categoria']),
                ':descripcion'  => trim($data['descripcion'] ?? '')
            ]);

            $id_categoria = (int)$this->db->lastInsertId();

            // Crear campos si se proporcionan
            if (!empty($data['campos']) && is_array($data['campos'])) {
                foreach ($data['campos'] as $campo) {
                    $this->crearCampo($id_categoria, $campo);
                }
            }

            $this->db->commit();
            return $id_categoria;

        } catch (PDOException $e) {
            $this->db->rollBack();
            logger("Error creando categoría: " . $e->getMessage(), 'ERROR');
            return false;
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error validación categoría: " . $e->getMessage(), 'WARNING');
            throw $e;
        }
    }

    /**
     * Actualizar categoría
     * 
     * @param int $id_categoria ID de la categoría
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó
     */
    public function actualizar($id_categoria, $data)
    {
        try {
            $updates = [];
            $params = [':id' => $id_categoria];

            $updatable = ['nombre_categoria', 'descripcion', 'estado'];

            foreach ($data as $key => $value) {
                if (in_array($key, $updatable) && $value !== null) {
                    if ($key === 'nombre_categoria') {
                        $existing = $this->db->prepare(
                            "SELECT id_categoria FROM " . self::TABLE . " WHERE nombre_categoria = :nombre AND id_categoria != :id"
                        );
                        $existing->execute([':nombre' => $value, ':id' => $id_categoria]);
                        if ($existing->fetch()) {
                            throw new \Exception("El nombre ya existe");
                        }
                    }

                    $updates[] = "$key = :$key";
                    $params[":$key"] = is_string($value) ? trim($value) : $value;
                }
            }

            if (empty($updates)) {
                return true;
            }

            $sql = "UPDATE " . self::TABLE . " SET " . implode(', ', $updates) . " WHERE id_categoria = :id";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute($params);

        } catch (PDOException $e) {
            logger("Error actualizando categoría: " . $e->getMessage(), 'ERROR');
            return false;
        } catch (\Exception $e) {
            logger("Error validación categoría: " . $e->getMessage(), 'WARNING');
            throw $e;
        }
    }

    /**
     * Obtener campos dinámicos de una categoría
     * 
     * @param int $id_categoria ID de la categoría
     * @return array Arreglo de campos
     */
    public function obtenerCampos($id_categoria)
    {
        $sql = "SELECT * FROM " . self::TABLE_CAMPOS . "
                WHERE id_categoria = :id
                ORDER BY orden_visualizacion ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_categoria]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear campo dinámico para una categoría
     * 
     * @param int $id_categoria ID de la categoría
     * @param array $data Datos del campo
     *   - nombre_campo (requerido)
     *   - tipo_dato (requerido)
     *   - es_obligatorio (opcional)
     *   - orden_visualizacion (opcional)
     *   - longitud_maxima (opcional)
     * 
     * @return int|false ID del campo creado
     */
    public function crearCampo($id_categoria, $data)
    {
        try {
            $required = ['nombre_campo', 'tipo_dato'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Campo requerido faltante: $field");
                }
            }

            $tipos_validos = ['texto_corto', 'texto_largo', 'numero_entero', 'numero_decimal', 'fecha', 'booleano'];
            if (!in_array($data['tipo_dato'], $tipos_validos)) {
                throw new \Exception("Tipo de dato inválido");
            }

            $sql = "INSERT INTO " . self::TABLE_CAMPOS . "
                    (id_categoria, nombre_campo, tipo_dato, es_obligatorio, orden_visualizacion, longitud_maxima)
                    VALUES (:id_cat, :nombre, :tipo, :obligatorio, :orden, :longitud)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_cat'       => $id_categoria,
                ':nombre'       => trim($data['nombre_campo']),
                ':tipo'         => $data['tipo_dato'],
                ':obligatorio'  => (bool)($data['es_obligatorio'] ?? false),
                ':orden'        => (int)($data['orden_visualizacion'] ?? 1),
                ':longitud'     => $data['longitud_maxima'] ?? null
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            logger("Error creando campo: " . $e->getMessage(), 'ERROR');
            return false;
        } catch (\Exception $e) {
            logger("Error validación campo: " . $e->getMessage(), 'WARNING');
            throw $e;
        }
    }

    /**
     * Eliminar campo dinámico
     * 
     * @param int $id_columna ID del campo
     * @return bool True si se eliminó
     */
    public function eliminarCampo($id_columna)
    {
        try {
            $sql = "DELETE FROM " . self::TABLE_CAMPOS . " WHERE id_columna = :id";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([':id' => $id_columna]);

        } catch (PDOException $e) {
            logger("Error eliminando campo: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Cambiar estado de categoría
     * 
     * @param int $id_categoria ID de la categoría
     * @param string $estado Nuevo estado
     * @return bool True si cambió
     */
    public function cambiarEstado($id_categoria, $estado)
    {
        $estados_validos = ['activa', 'obsoleta'];
        
        if (!in_array($estado, $estados_validos)) {
            throw new \Exception("Estado inválido");
        }

        $sql = "UPDATE " . self::TABLE . " SET estado = :estado WHERE id_categoria = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':estado' => $estado,
            ':id' => $id_categoria
        ]);
    }
}
?>
