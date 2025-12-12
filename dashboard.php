<?php
/**
 * Punto de entrada para el Dashboard - SDI Gestión Documental
 * Redirige al controlador del dashboard
 */

require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/controllers/DashboardController.php';

$controller = new DashboardController();
$controller->mostrar();

