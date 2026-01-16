<?php
namespace App\Middleware;

use App\Models\Usuario;

/**
 * Middleware: Autenticación
 * 
 * Verifica que el usuario esté autenticado.
 * Requiere una sesión válida con id_usuario y rol.
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class Autenticacion
{
    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool True si está autenticado
     */
    public static function verificar()
    {
        logger("AUTH: Verificando sesión activa...", 'DEBUG');
        
        $hasId = isset($_SESSION['id_usuario']);
        $hasEmail = isset($_SESSION['email']);
        $hasRol = isset($_SESSION['rol']);
        
        logger("AUTH: Session check - id_usuario: " . ($hasId ? "✓" : "✗") . ", email: " . ($hasEmail ? "✓" : "✗") . ", rol: " . ($hasRol ? "✓" : "✗"), 'DEBUG');
        
        if ($hasId && $hasEmail && $hasRol) {
            logger("AUTH: ✓ Sesión válida - ID: " . $_SESSION['id_usuario'] . ", Email: " . $_SESSION['email'], 'DEBUG');
            return true;
        }
        
        logger("AUTH: ✗ Sesión inválida - faltan datos", 'DEBUG');
        return false;
    }

    /**
     * Obtener ID del usuario autenticado
     * 
     * @return int|null ID del usuario o null
     */
    public static function getId()
    {
        return $_SESSION['id_usuario'] ?? null;
    }

    /**
     * Obtener email del usuario autenticado
     * 
     * @return string|null Email del usuario
     */
    public static function getEmail()
    {
        return $_SESSION['email'] ?? null;
    }

    /**
     * Obtener rol del usuario autenticado
     * 
     * @return string|null Nombre del rol
     */
    public static function getRol()
    {
        return $_SESSION['rol'] ?? null;
    }

    /**
     * Obtener datos completos del usuario autenticado
     * 
     * @return array|null Datos del usuario
     */
    public static function usuario()
    {
        if (!self::verificar()) {
            return null;
        }

        return [
            'id_usuario' => self::getId(),
            'email'      => self::getEmail(),
            'rol'        => self::getRol(),
            'nombre'     => $_SESSION['nombre'] ?? '',
            'apellidos'  => $_SESSION['apellidos'] ?? ''
        ];
    }

    /**
     * Iniciar sesión de usuario
     * 
     * @param array $usuario Datos del usuario (id_usuario, email, rol, nombre, apellido_paterno, apellido_materno)
     */
    public static function iniciar($usuario)
    {
        try {
            logger("AUTH: Iniciando sesión - Datos recibidos: ID=" . ($usuario['id_usuario'] ?? 'NULL') . ", Email=" . ($usuario['email'] ?? 'NULL'), 'DEBUG');
            
            $_SESSION['id_usuario'] = $usuario['id_usuario'] ?? null;
            logger("AUTH: ✓ Asignado SESSION id_usuario: " . ($_SESSION['id_usuario'] ?? 'NULL'), 'DEBUG');
            
            $_SESSION['email'] = $usuario['email'] ?? '';
            logger("AUTH: ✓ Asignado SESSION email: " . ($_SESSION['email'] ?? 'NULL'), 'DEBUG');
            
            $_SESSION['rol'] = $usuario['nombre_rol'] ?? '';
            logger("AUTH: ✓ Asignado SESSION rol: " . ($_SESSION['rol'] ?? 'NULL'), 'DEBUG');
            
            $_SESSION['nombre'] = $usuario['nombre'] ?? '';
            logger("AUTH: ✓ Asignado SESSION nombre: " . ($_SESSION['nombre'] ?? 'NULL'), 'DEBUG');
            
            $_SESSION['apellidos'] = ($usuario['apellido_paterno'] ?? '') . 
                                     (!empty($usuario['apellido_materno']) ? ' ' . $usuario['apellido_materno'] : '');
            logger("AUTH: ✓ Asignado SESSION apellidos: " . ($_SESSION['apellidos'] ?? 'NULL'), 'DEBUG');
            
            // Registrar login en logs
            logger("AUTH: ✓ Usuario {$usuario['email']} inició sesión correctamente", 'INFO', ['user_id' => $_SESSION['id_usuario']]);
            
        } catch (Exception $e) {
            logger("AUTH: ✗ Error al iniciar sesión - " . $e->getMessage(), 'ERROR', ['user_email' => ($usuario['email'] ?? 'NULL')]);
            throw $e;
        }
    }

    /**
     * Cerrar sesión
     */
    public static function cerrar()
    {
        $email = $_SESSION['email'] ?? 'desconocido';
        logger("Usuario $email cerró sesión", 'INFO');
        
        session_destroy();
    }

    /**
     * Redirigir si no está autenticado
     * 
     * @param string $redirectUrl URL de redirección
     */
    public static function requerirAutenticacion($redirectUrl = '/login.html')
    {
        if (!self::verificar()) {
            http_response_code(401);
            header("Location: " . BASE_URL . ltrim($redirectUrl, '/'));
            exit;
        }
    }
}
?>
