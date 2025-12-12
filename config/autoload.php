<?php
/**
 * Autoloader y Bootstrap - SDI Gestión Documental
 * Carga automática de clases y configuración inicial
 */

// Definir raíz de la aplicación
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Cargar constantes
require_once APP_ROOT . '/config/constants.php';

// Cargar helper de seguridad
require_once APP_ROOT . '/helpers/seguridad.php';

// Cargar conexión a base de datos
require_once APP_ROOT . '/config/db.php';

// Autoloader simple para clases
spl_autoload_register(function ($className) {
    $directories = [
        APP_ROOT . '/models/',
        APP_ROOT . '/controllers/',
        APP_ROOT . '/helpers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Iniciar sesión segura
startSecureSession();

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Configurar manejo de errores (en producción, desactivar display_errors)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

