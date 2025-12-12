<?php
/**
 * Modelo Usuario - SDI Gestión Documental
 * Maneja todas las operaciones relacionadas con usuarios
 * 
 * Seguridad: Todas las consultas usan Prepared Statements
 */

require_once __DIR__ . '/../config/autoload.php';

class Usuario {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Busca un usuario por email
     * 
     * @param string $email Email del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    public function buscarPorEmail(string $email) {
        try {
            $sql = "SELECT u.id_usuario, u.nombre_completo, u.email, u.password_hash, 
                           u.id_rol, u.estado, u.fecha_registro,
                           r.nombre_rol, r.descripcion as rol_descripcion
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    WHERE u.email = :email
                    LIMIT 1";
            
            $stmt = executeQuery($sql, ['email' => $email]);
            $usuario = $stmt->fetch();
            
            return $usuario ? $usuario : false;
            
        } catch (PDOException $e) {
            error_log("Error al buscar usuario por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca un usuario por ID
     * 
     * @param int $id_usuario ID del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    public function buscarPorId(int $id_usuario) {
        try {
            $sql = "SELECT u.id_usuario, u.nombre_completo, u.email, 
                           u.id_rol, u.estado, u.fecha_registro,
                           r.nombre_rol, r.descripcion as rol_descripcion
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = :id_usuario
                    LIMIT 1";
            
            $stmt = executeQuery($sql, ['id_usuario' => $id_usuario]);
            $usuario = $stmt->fetch();
            
            return $usuario ? $usuario : false;
            
        } catch (PDOException $e) {
            error_log("Error al buscar usuario por ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica las credenciales de un usuario
     * 
     * @param string $email Email del usuario
     * @param string $password Contraseña en texto plano
     * @return array|false Datos del usuario si las credenciales son correctas, false en caso contrario
     */
    public function verificarCredenciales(string $email, string $password) {
        $usuario = $this->buscarPorEmail($email);
        
        if (!$usuario) {
            return false;
        }
        
        // Verificar que el usuario esté activo
        if ($usuario['estado'] !== ESTADO_ACTIVO) {
            return false;
        }
        
        // Verificar contraseña usando password_verify()
        if (!password_verify($password, $usuario['password_hash'])) {
            return false;
        }
        
        // Eliminar password_hash del array antes de retornar
        unset($usuario['password_hash']);
        
        return $usuario;
    }
    
    /**
     * Crea un nuevo usuario
     * 
     * @param array $datos Datos del usuario ['nombre_completo', 'email', 'password', 'id_rol']
     * @return int|false ID del usuario creado o false si hay error
     */
    public function crear(array $datos) {
        try {
            // Validar datos requeridos
            $camposRequeridos = ['nombre_completo', 'email', 'password', 'id_rol'];
            foreach ($camposRequeridos as $campo) {
                if (!isset($datos[$campo]) || empty($datos[$campo])) {
                    return false;
                }
            }
            
            // Verificar que el email no exista
            if ($this->buscarPorEmail($datos['email'])) {
                return false; // Email ya existe
            }
            
            // Hash de la contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, id_rol, estado)
                    VALUES (:nombre_completo, :email, :password_hash, :id_rol, :estado)";
            
            $params = [
                'nombre_completo' => sanitizeInput($datos['nombre_completo']),
                'email' => sanitizeInput($datos['email']),
                'password_hash' => $passwordHash,
                'id_rol' => validateInt($datos['id_rol']),
                'estado' => ESTADO_ACTIVO
            ];
            
            $stmt = executeQuery($sql, $params);
            
            return $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $id_usuario ID del usuario
     * @param string $nuevaPassword Nueva contraseña en texto plano
     * @return bool True si se actualizó correctamente
     */
    public function actualizarPassword(int $id_usuario, string $nuevaPassword) {
        try {
            $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE usuarios 
                    SET password_hash = :password_hash
                    WHERE id_usuario = :id_usuario";
            
            $params = [
                'password_hash' => $passwordHash,
                'id_usuario' => $id_usuario
            ];
            
            $stmt = executeQuery($sql, $params);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza el estado de un usuario
     * 
     * @param int $id_usuario ID del usuario
     * @param string $estado Nuevo estado ('activo' o 'inactivo')
     * @return bool True si se actualizó correctamente
     */
    public function actualizarEstado(int $id_usuario, string $estado) {
        try {
            if (!in_array($estado, [ESTADO_ACTIVO, ESTADO_INACTIVO])) {
                return false;
            }
            
            $sql = "UPDATE usuarios 
                    SET estado = :estado
                    WHERE id_usuario = :id_usuario";
            
            $params = [
                'estado' => $estado,
                'id_usuario' => $id_usuario
            ];
            
            $stmt = executeQuery($sql, $params);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los usuarios (con paginación opcional)
     * 
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Lista de usuarios
     */
    public function listar(int $limit = 50, int $offset = 0) {
        try {
            $sql = "SELECT u.id_usuario, u.nombre_completo, u.email, 
                           u.id_rol, u.estado, u.fecha_registro,
                           r.nombre_rol, r.descripcion as rol_descripcion
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    ORDER BY u.fecha_registro DESC
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                'limit' => $limit,
                'offset' => $offset
            ];
            
            $stmt = executeQuery($sql, $params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al listar usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cuenta el total de usuarios
     * 
     * @return int Total de usuarios
     */
    public function contar() {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = executeQuery($sql);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
            
        } catch (PDOException $e) {
            error_log("Error al contar usuarios: " . $e->getMessage());
            return 0;
        }
    }
}

