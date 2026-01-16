<?php
/**
 * Configuración de Base de Datos y Constantes - SDI Gestión Documental
 */

// Prevenir acceso directo
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// ==========================================
// 2. CONSTANTES DEL SISTEMA
// ==========================================

// Detectar automáticamente si estamos en subcarpeta (Corrección para XAMPP)
// Esto evita que BASE_URL sea '/' cuando debería ser '/Programa-Gestion-SDI/'
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = ($scriptDir === '/' || $scriptDir === '\\') ? '/' : $scriptDir . '/';

if (!defined('BASE_URL')) define('BASE_URL', $baseUrl);
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', APP_ROOT . '/public/uploads/');
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', BASE_URL . 'uploads/');

if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5242880); // 5MB
if (!defined('ALLOWED_EXTENSIONS')) define('ALLOWED_EXTENSIONS', ['pdf', 'docx', 'doc', 'jpg', 'png']);

// ==========================================
// 3. ROLES (Deben coincidir con la Base de Datos)
// ==========================================
if (!defined('ROL_ADMINISTRADOR')) define('ROL_ADMINISTRADOR', 'Administrador');
if (!defined('ROL_ESTUDIANTE')) define('ROL_ESTUDIANTE', 'Estudiante SS');
if (!defined('ROL_ADMINISTRATIVO')) define('ROL_ADMINISTRATIVO', 'Personal Administrativo');

// Estados
if (!defined('ESTADO_ACTIVO')) define('ESTADO_ACTIVO', 'activo');
if (!defined('ESTADO_INACTIVO')) define('ESTADO_INACTIVO', 'inactivo');
?>