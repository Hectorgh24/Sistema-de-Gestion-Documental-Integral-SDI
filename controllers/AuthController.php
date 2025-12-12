<?php
/**
 * Controlador de Autenticación - SDI Gestión Documental
 * Maneja login, logout y validación de sesiones
 * 
 * Seguridad: Validación doble (cliente y servidor), protección CSRF
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Muestra el formulario de login
     */
    public function mostrarLogin() {
        // Si ya está autenticado, redirigir al dashboard
        if (isAuthenticated()) {
            $this->redirigirSegunRol();
            return;
        }
        
        $error = getGet('error', '');
        $mensaje = getGet('mensaje', '');
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Procesa el login
     */
    public function procesarLogin() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login.php');
            exit;
        }
        
        // Validación del lado del servidor (autoridad final)
        $errores = $this->validarLogin();
        
        if (!empty($errores)) {
            $_SESSION['error_login'] = $errores;
            header('Location: /login.php?error=' . urlencode(implode(', ', $errores)));
            exit;
        }
        
        // Obtener y sanitizar datos
        $email = getPost('email');
        $password = getPost('password');
        
        // Verificar credenciales
        $usuario = $this->usuarioModel->verificarCredenciales($email, $password);
        
        if (!$usuario) {
            // No revelar si el email existe o no (seguridad)
            $_SESSION['error_login'] = ['Credenciales inválidas'];
            header('Location: /login.php?error=' . urlencode('Credenciales inválidas'));
            exit;
        }
        
        // Login exitoso - Regenerar ID de sesión (prevenir Session Fixation)
        regenerateSessionId();
        
        // Establecer datos de sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['nombre_rol'];
        $_SESSION['usuario_id_rol'] = $usuario['id_rol'];
        $_SESSION['login_time'] = time();
        
        // Redirigir según el rol
        $this->redirigirSegunRol();
    }
    
    /**
     * Valida los datos del formulario de login
     * 
     * @return array Array de errores (vacío si no hay errores)
     */
    private function validarLogin(): array {
        $errores = [];
        
        // Validar email
        $email = getPost('email');
        if (empty($email)) {
            $errores[] = 'El email es requerido';
        } elseif (!validateEmail($email)) {
            $errores[] = 'El email no es válido';
        }
        
        // Validar contraseña
        $password = getPost('password');
        if (empty($password)) {
            $errores[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        return $errores;
    }
    
    /**
     * Redirige al usuario según su rol
     */
    private function redirigirSegunRol() {
        if (!isset($_SESSION['usuario_rol'])) {
            header('Location: /login.php');
            exit;
        }
        
        $rol = $_SESSION['usuario_rol'];
        
        switch ($rol) {
            case ROL_ADMINISTRADOR:
                header('Location: /dashboard.php');
                break;
            case ROL_ACADEMICO:
                header('Location: /dashboard.php');
                break;
            case ROL_ALUMNO:
                header('Location: /dashboard.php');
                break;
            default:
                header('Location: /login.php');
        }
        exit;
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        startSecureSession();
        
        // Destruir todas las variables de sesión
        $_SESSION = [];
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Redirigir al login
        header('Location: /login.php?mensaje=' . urlencode('Sesión cerrada correctamente'));
        exit;
    }
    
    /**
     * Verifica si el usuario está autenticado y tiene el rol requerido
     * 
     * @param string|array|null $roles Rol o roles permitidos (null = cualquier rol autenticado)
     * @return bool True si está autenticado y tiene el rol
     */
    public function verificarAcceso($roles = null): bool {
        if (!isAuthenticated()) {
            return false;
        }
        
        if ($roles === null) {
            return true;
        }
        
        $rolesPermitidos = is_array($roles) ? $roles : [$roles];
        $rolUsuario = $_SESSION['usuario_rol'] ?? '';
        
        return in_array($rolUsuario, $rolesPermitidos);
    }
}

