<?php
/**
 * Punto de entrada para autenticación - SDI Gestión Documental
 * Router simple para el módulo de autenticación
 */

require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/controllers/AuthController.php';

$authController = new AuthController();

// Determinar acción según método y parámetros
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'logout':
        $authController->logout();
        break;
    
    case 'login':
    default:
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->procesarLogin();
        } else {
            $authController->mostrarLogin();
        }
        break;
}

