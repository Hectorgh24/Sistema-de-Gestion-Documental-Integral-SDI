<?php
// Este archivo ejecuta la migración de agregar el campo titulo
// Accede a través de: http://localhost/Programa-Gestion-SDI/database/migrate.php

// Proteger contra acceso no autorizado
$token = $_GET['token'] ?? '';
if ($token !== 'migrate2024') {
    header('HTTP/1.1 403 Forbidden');
    die('Acceso denegado');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

try {
    $db = getDBConnection();
    
    // Verificar si la columna ya existe
    $result = $db->query("SHOW COLUMNS FROM carpetas_fisicas LIKE 'titulo'");
    
    if ($result->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'El campo "titulo" ya existe en la tabla'
        ]);
        exit;
    }
    
    // Agregar columna titulo
    $db->exec("ALTER TABLE carpetas_fisicas 
              ADD COLUMN titulo VARCHAR(150) UNIQUE 
              AFTER no_carpeta_fisica");
    
    // Generar títulos para carpetas existentes
    $db->exec("UPDATE carpetas_fisicas 
              SET titulo = CONCAT('Carpeta ', no_carpeta_fisica)
              WHERE titulo IS NULL");
    
    echo json_encode([
        'success' => true,
        'message' => 'Migración completada exitosamente',
        'field_added' => true
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la migración: ' . $e->getMessage()
    ]);
    exit(1);
}
?>
