<?php
/**
 * Script de migración: Agregar campo titulo a carpetas_fisicas
 * 
 * Este script agrega el campo 'titulo' a la tabla carpetas_fisicas
 * y genera títulos automáticos para las carpetas existentes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración
require_once __DIR__ . '/../config/db.php';

try {
    $db = getDBConnection();
    
    echo "🔄 Iniciando migración...\n";
    
    // 1. Verificar si la columna ya existe
    $result = $db->query("SHOW COLUMNS FROM carpetas_fisicas LIKE 'titulo'");
    
    if ($result->rowCount() > 0) {
        echo "✅ El campo 'titulo' ya existe en la tabla.\n";
    } else {
        echo "📝 Agregando el campo 'titulo' a carpetas_fisicas...\n";
        
        // Agregar columna
        $db->exec("ALTER TABLE carpetas_fisicas 
                   ADD COLUMN titulo VARCHAR(150) NOT NULL UNIQUE 
                   AFTER no_carpeta_fisica");
        
        echo "✅ Campo 'titulo' agregado correctamente.\n";
        
        // Generar títulos para carpetas existentes
        echo "📝 Generando títulos para carpetas existentes...\n";
        
        $db->exec("UPDATE carpetas_fisicas 
                  SET titulo = CONCAT('Carpeta ', no_carpeta_fisica, ' - ', etiqueta_identificadora)
                  WHERE titulo IS NULL OR titulo = ''");
        
        echo "✅ Títulos generados correctamente.\n";
    }
    
    // Verificar estructura
    echo "\n📊 Estructura actualizada de carpetas_fisicas:\n";
    $result = $db->query("SHOW COLUMNS FROM carpetas_fisicas");
    
    foreach ($result as $row) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    // Mostrar datos de prueba
    echo "\n📋 Datos actuales (primeras 5 carpetas):\n";
    $result = $db->query("SELECT no_carpeta_fisica, titulo, etiqueta_identificadora FROM carpetas_fisicas LIMIT 5");
    
    foreach ($result as $row) {
        echo "  - #{$row['no_carpeta_fisica']}: {$row['titulo']} ({$row['etiqueta_identificadora']})\n";
    }
    
    echo "\n✅ ¡Migración completada exitosamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error en la migración: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    exit(1);
}
?>
