<?php
/**
 * Constantes de la Aplicación - SDI Gestión Documental
 */

// Prevenir acceso directo
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Rutas de la aplicación
define('BASE_URL', '/');
define('UPLOAD_DIR', APP_ROOT . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Configuración de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes
define('ALLOWED_EXTENSIONS', ['pdf', 'docx']);

// Roles del sistema
define('ROL_ADMINISTRADOR', 'Administrador');
define('ROL_ACADEMICO', 'Academico');
define('ROL_ALUMNO', 'Alumno');

// Estados
define('ESTADO_ACTIVO', 'activo');
define('ESTADO_INACTIVO', 'inactivo');
define('RESPALDO_PENDIENTE', 'pendiente');
define('RESPALDO_RESPALDADO', 'respaldado');

// Configuración de sesión
define('SESSION_TIMEOUT', 1800); // 30 minutos en segundos

