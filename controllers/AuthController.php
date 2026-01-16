<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Middleware\Autenticacion;

/**
 * Controller: AuthController
 * 
 * Gestiona el flujo de autenticación:
 * - Login
 * - Logout
 * - Validación de credenciales
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class AuthController
{
    protected $usuarioModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Procesar login
     * 
     * Recibe POST con email y password
     * Valida credenciales contra base de datos
     * Crea sesión si es correcto
     * 
     * @return void JSON response
     */
    public function login()
    {
        try {
            logger("=== INICIO PROCESO LOGIN ===", 'DEBUG');
            
            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                logger("Método HTTP inválido: " . $_SERVER['REQUEST_METHOD'], 'WARNING');
                response(false, 'Método no permitido', null, 405);
            }

            // Obtener datos JSON del body
            $rawInput = file_get_contents('php://input');
            logger("Raw input recibido: " . strlen($rawInput) . " bytes", 'DEBUG');
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                $jsonError = json_last_error_msg();
                logger("Error decodificando JSON: $jsonError", 'ERROR', ['raw' => substr($rawInput, 0, 100)]);
                response(false, 'Datos inválidos (JSON inválido)', null, 400);
            }

            // Validar campos requeridos
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';

            logger("Intento de login con email: $email", 'DEBUG');

            if (empty($email) || empty($password)) {
                logger("Campos faltantes - email: " . (empty($email) ? 'VACIO' : 'OK') . ", password: " . (empty($password) ? 'VACIO' : 'OK'), 'WARNING');
                response(false, 'Email y contraseña son requeridos', null, 400);
            }

            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                logger("Formato de email inválido: $email", 'WARNING');
                response(false, 'Formato de email inválido', null, 400);
            }

            // Buscar usuario por email
            logger("Buscando usuario en BD con email: $email", 'DEBUG');
            $usuario = $this->usuarioModel->obtenerPorEmail(strtolower($email));

            if (!$usuario) {
                logger("Usuario NO encontrado en BD: $email", 'WARNING');
                response(false, 'Email o contraseña incorrectos', null, 401);
            }

            logger("Usuario encontrado en BD - ID: " . ($usuario['id_usuario'] ?? 'NULL') . ", Estado: " . ($usuario['estado'] ?? 'NULL'), 'DEBUG');

            // Verificar que el usuario esté activo
            if ($usuario['estado'] !== 'activo') {
                logger("Usuario inactivo o suspendido: $email (Estado: " . ($usuario['estado'] ?? 'NULL') . ")", 'WARNING');
                response(false, 'Usuario inactivo o suspendido', null, 403);
            }

            // Verificar contraseña
            logger("Verificando contraseña del usuario", 'DEBUG');
            if (!password_verify($password, $usuario['password_hash'] ?? '')) {
                logger("Contraseña incorrecta para usuario: $email", 'WARNING');
                response(false, 'Email o contraseña incorrectos', null, 401);
            }

            logger("✓ Contraseña verificada correctamente", 'DEBUG');

            // Iniciar sesión
            logger("Iniciando sesión para usuario: $email", 'DEBUG');
            Autenticacion::iniciar($usuario);
            logger("✓ Sesión iniciada correctamente", 'DEBUG');

            // Retornar datos del usuario (sin contraseña)
            $datosUsuario = [
                'id_usuario' => $usuario['id_usuario'] ?? null,
                'email' => $usuario['email'] ?? '',
                'nombre' => $usuario['nombre'] ?? '',
                'apellidos' => ($usuario['apellido_paterno'] ?? '') . 
                             (!empty($usuario['apellido_materno']) ? ' ' . $usuario['apellido_materno'] : ''),
                'rol' => $usuario['nombre_rol'] ?? ''
            ];

            logger("Login exitoso para usuario: $email", 'INFO', ['usuario_id' => $datosUsuario['id_usuario'], 'rol' => $datosUsuario['rol']]);
            response(true, 'Login exitoso', $datosUsuario, 200);

        } catch (\Exception $e) {
            logger("Error en login: " . $e->getMessage(), 'ERROR', [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            response(false, 'Error en el servidor: ' . $e->getMessage(), null, 500);
        } catch (\Throwable $e) {
            logger("Error fatal en login: " . $e->getMessage(), 'ERROR', [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            response(false, 'Error crítico en el servidor', null, 500);
        }
    }

    /**
     * Verificar si usuario está autenticado
     * 
     * @return void JSON response
     */
    public function verificar()
    {
        try {
            logger("=== INICIO VERIFICACIÓN DE AUTENTICACIÓN ===", 'DEBUG');
            logger("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'], 'DEBUG');
            logger("Session ID: " . session_id(), 'DEBUG');
            logger("Session data: " . json_encode($_SESSION ?? []), 'DEBUG');
            
            $verificado = Autenticacion::verificar();
            logger("Resultado de verificación: " . ($verificado ? 'TRUE' : 'FALSE'), 'DEBUG');
            
            if (!$verificado) {
                logger("Usuario NO autenticado - retornando 401", 'DEBUG');
                response(false, 'No autenticado', null, 401);
            }

            $usuario = Autenticacion::usuario();
            logger("Usuario autenticado - ID: " . ($usuario['id_usuario'] ?? 'NULL') . ", Email: " . ($usuario['email'] ?? 'NULL'), 'DEBUG');
            response(true, 'Autenticado', $usuario, 200);

        } catch (\Exception $e) {
            logger("Error verificando autenticación: " . $e->getMessage(), 'ERROR', [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            response(false, 'Error en el servidor: ' . $e->getMessage(), null, 500);
        } catch (\Throwable $e) {
            logger("Error fatal verificando autenticación: " . $e->getMessage(), 'ERROR', [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            response(false, 'Error crítico en el servidor', null, 500);
        }
    }

    /**
     * Procesar logout
     * 
     * @return void JSON response
     */
    public function logout()
    {
        try {
            Autenticacion::cerrar();
            response(true, 'Sesión cerrada', null, 200);

        } catch (\Exception $e) {
            logger("Error en logout: " . $e->getMessage(), 'ERROR');
            response(false, 'Error en el servidor', null, 500);
        }
    }

    /**
     * Obtener datos del usuario autenticado
     * 
     * @return void JSON response
     */
    public function perfil()
    {
        try {
            Autenticacion::requerirAutenticacion();

            $usuario = Autenticacion::usuario();
            response(true, 'Datos del usuario', $usuario, 200);

        } catch (\Exception $e) {
            logger("Error obteniendo perfil: " . $e->getMessage(), 'ERROR');
            response(false, 'Error en el servidor', null, 500);
        }
    }

    /**
     * Cambiar contraseña del usuario autenticado
     * 
     * @return void JSON response
     */
    public function cambiarPassword()
    {
        try {
            // Verificar autenticación
            Autenticacion::requerirAutenticacion();

            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                response(false, 'Método no permitido', null, 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                response(false, 'Datos inválidos', null, 400);
            }

            $password_actual = $input['password_actual'] ?? '';
            $password_nueva = $input['password_nueva'] ?? '';
            $password_confirma = $input['password_confirma'] ?? '';

            // Validaciones
            if (empty($password_actual) || empty($password_nueva) || empty($password_confirma)) {
                response(false, 'Todos los campos son requeridos', null, 400);
            }

            if ($password_nueva !== $password_confirma) {
                response(false, 'Las contraseñas nuevas no coinciden', null, 400);
            }

            if (strlen($password_nueva) < 8) {
                response(false, 'La contraseña debe tener al menos 8 caracteres', null, 400);
            }

            if ($password_actual === $password_nueva) {
                response(false, 'La nueva contraseña no puede ser igual a la actual', null, 400);
            }

            // Cambiar contraseña
            $id_usuario = Autenticacion::getId();
            $this->usuarioModel->cambiarPassword($id_usuario, $password_actual, $password_nueva);

            logger("Usuario cambió contraseña: " . Autenticacion::getEmail(), 'INFO');
            response(true, 'Contraseña actualizada exitosamente', null, 200);

        } catch (\Exception $e) {
            logger("Error cambiar password: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }
}
?>
