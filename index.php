<?php
/**
 * Router Principal - SDI Gestión Documental
 * Punto de entrada de la aplicación
 * 
 * Maneja el enrutamiento básico de la aplicación
 */

require_once __DIR__ . '/config/autoload.php';

// Obtener la ruta solicitada
$ruta = $_SERVER['REQUEST_URI'] ?? '/';
$ruta = parse_url($ruta, PHP_URL_PATH);
$ruta = rtrim($ruta, '/') ?: '/';

// Si la ruta es el root, redirigir al dashboard si está autenticado, sino al login
if ($ruta === '/') {
    if (isAuthenticated()) {
        header('Location: /dashboard.php');
        exit;
    } else {
        header('Location: /login.php');
        exit;
    }
}

// Si la ruta es dashboard.php, cargar el dashboard
if ($ruta === '/dashboard.php' || strpos($ruta, '/dashboard') === 0) {
    require_once __DIR__ . '/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->mostrar();
    exit;
}

// Si la ruta es login.php, cargar el login
if ($ruta === '/login.php' || strpos($ruta, '/login') === 0) {
    require_once __DIR__ . '/login.php';
    exit;
}

// Para otras rutas, intentar cargar el archivo correspondiente
// Si no existe, mostrar 404
if (file_exists(__DIR__ . $ruta) && is_file(__DIR__ . $ruta)) {
    require_once __DIR__ . $ruta;
} else {
    http_response_code(404);
    echo "Página no encontrada";
}

