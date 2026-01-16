<?php
namespace App\Controllers;

use App\Models\Categoria;
use App\Models\Rol;
use App\Middleware\Autenticacion;
use App\Middleware\Autorizacion;

/**
 * Controller: CategoriaController
 * 
 * Gestiona categorías (tipos) de documentos.
 * Acceso: Administrativo y Admin
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class CategoriaController
{
    protected $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }

    public function listar()
    {
        try {
            Autenticacion::requerirAutenticacion();

            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, (int)($_GET['limit'] ?? 10));
            $offset = ($page - 1) * $limit;
            $solo_activas = $_GET['solo_activas'] ?? true;

            $categorias = $this->categoriaModel->listar($limit, $offset, $solo_activas);
            $total = $this->categoriaModel->contar($solo_activas);

            $data = [
                'categorias' => $categorias,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];

            response(true, 'Categorías obtenidas', $data, 200);
        } catch (\Exception $e) {
            logger("Error listando categorías: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    public function obtener($id)
    {
        try {
            Autenticacion::requerirAutenticacion();

            $categoria = $this->categoriaModel->obtenerPorId((int)$id);

            if (!$categoria) {
                response(false, 'Categoría no encontrada', null, 404);
            }

            response(true, 'Categoría obtenida', $categoria, 200);
        } catch (\Exception $e) {
            logger("Error obteniendo categoría: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    public function crear()
    {
        try {
            Autenticacion::requerirAutenticacion();
            Autorizacion::requerirAcceso('crear_categoria');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                response(false, 'Método no permitido', null, 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || empty($input['nombre_categoria'])) {
                response(false, 'Nombre de categoría requerido', null, 400);
            }

            $id_categoria = $this->categoriaModel->crear([
                'nombre_categoria' => $input['nombre_categoria'],
                'descripcion'      => $input['descripcion'] ?? '',
                'campos'           => $input['campos'] ?? []
            ]);

            if (!$id_categoria) {
                response(false, 'Error al crear categoría', null, 500);
            }

            $categoria = $this->categoriaModel->obtenerPorId($id_categoria);

            logger("Categoría creada: ID $id_categoria", 'INFO');
            response(true, 'Categoría creada', $categoria, 201);
        } catch (\Exception $e) {
            logger("Error creando categoría: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    public function actualizar($id)
    {
        try {
            Autenticacion::requerirAutenticacion();
            Autorizacion::requerirAcceso('editar_categoria');

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                response(false, 'Método no permitido', null, 405);
            }

            if (!$this->categoriaModel->obtenerPorId((int)$id)) {
                response(false, 'Categoría no encontrada', null, 404);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                response(false, 'Datos inválidos', null, 400);
            }

            $this->categoriaModel->actualizar((int)$id, $input);

            $categoria = $this->categoriaModel->obtenerPorId((int)$id);

            logger("Categoría actualizada: ID $id", 'INFO');
            response(true, 'Categoría actualizada', $categoria, 200);
        } catch (\Exception $e) {
            logger("Error actualizando categoría: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    public function eliminar($id)
    {
        try {
            Autenticacion::requerirAutenticacion();
            Autorizacion::requerirRol(Rol::ADMINISTRADOR);

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                response(false, 'Método no permitido', null, 405);
            }

            if (!$this->categoriaModel->obtenerPorId((int)$id)) {
                response(false, 'Categoría no encontrada', null, 404);
            }

            // Marcar como obsoleta en lugar de eliminar
            $this->categoriaModel->cambiarEstado((int)$id, 'obsoleta');

            logger("Categoría eliminada: ID $id", 'INFO');
            response(true, 'Categoría eliminada', null, 200);
        } catch (\Exception $e) {
            logger("Error eliminando categoría: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Obtener columnas de una categoría
     * GET /api/categorias/:id/columnas
     */
    public function columnas($id)
    {
        try {
            Autenticacion::requerirAutenticacion();

            if (!$this->categoriaModel->obtenerPorId((int)$id)) {
                response(false, 'Categoría no encontrada', null, 404);
            }

            $columnas = $this->categoriaModel->obtenerColumnas((int)$id);

            $data = [
                'columnas' => $columnas
            ];

            response(true, 'Columnas obtenidas', $data, 200);
        } catch (\Exception $e) {
            logger("Error obteniendo columnas: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }
}
?>
