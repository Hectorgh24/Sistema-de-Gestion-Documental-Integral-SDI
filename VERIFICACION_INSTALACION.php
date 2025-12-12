<?php
/**
 * Script de Verificación de Instalación - SDI Gestión Documental
 * 
 * Este script verifica que la instalación esté correcta.
 * 
 * IMPORTANTE: Eliminar este archivo después de verificar la instalación
 */

// Prevenir acceso si no se accede directamente
if (php_sapi_name() !== 'cli' && !isset($_GET['token']) || $_GET['token'] !== 'verificar123') {
    die('Acceso denegado. Usa: ?token=verificar123');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Instalación - SDI</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .ok { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 0; }
        ul { list-style: none; padding-left: 0; }
        li { padding: 5px 0; }
    </style>
</head>
<body>
    <h1>🔍 Verificación de Instalación - SDI Gestión Documental</h1>
    
    <?php
    $errores = [];
    $advertencias = [];
    $exitos = [];
    
    // 1. Verificar PHP
    echo '<div class="section">';
    echo '<h2>1. Versión de PHP</h2>';
    $phpVersion = phpversion();
    if (version_compare($phpVersion, '8.0.0', '>=')) {
        echo '<p class="ok">✅ PHP ' . $phpVersion . ' (Compatible)</p>';
        $exitos[] = 'PHP';
    } else {
        echo '<p class="error">❌ PHP ' . $phpVersion . ' (Se requiere PHP 8.0+)</p>';
        $errores[] = 'PHP';
    }
    echo '</div>';
    
    // 2. Verificar extensiones
    echo '<div class="section">';
    echo '<h2>2. Extensiones PHP Requeridas</h2>';
    $extensiones = ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo', 'session'];
    foreach ($extensiones as $ext) {
        if (extension_loaded($ext)) {
            echo '<p class="ok">✅ ' . $ext . '</p>';
        } else {
            echo '<p class="error">❌ ' . $ext . ' (No instalada)</p>';
            $errores[] = $ext;
        }
    }
    echo '</div>';
    
    // 3. Verificar estructura de directorios
    echo '<div class="section">';
    echo '<h2>3. Estructura de Directorios</h2>';
    $directorios = [
        'config',
        'controllers',
        'models',
        'views',
        'helpers',
        'public',
        'public/uploads',
        'database'
    ];
    
    foreach ($directorios as $dir) {
        if (is_dir($dir)) {
            $permisos = substr(sprintf('%o', fileperms($dir)), -4);
            echo '<p class="ok">✅ ' . $dir . ' (Permisos: ' . $permisos . ')</p>';
        } else {
            echo '<p class="error">❌ ' . $dir . ' (No existe)</p>';
            $errores[] = $dir;
        }
    }
    echo '</div>';
    
    // 4. Verificar archivos importantes
    echo '<div class="section">';
    echo '<h2>4. Archivos Importantes</h2>';
    $archivos = [
        'config/db.php',
        'config/constants.php',
        'config/autoload.php',
        'helpers/seguridad.php',
        'index.php',
        'login.php',
        'dashboard.php',
        '.htaccess'
    ];
    
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            echo '<p class="ok">✅ ' . $archivo . '</p>';
        } else {
            echo '<p class="error">❌ ' . $archivo . ' (No existe)</p>';
            $errores[] = $archivo;
        }
    }
    echo '</div>';
    
    // 5. Verificar permisos de uploads
    echo '<div class="section">';
    echo '<h2>5. Permisos de Directorio Uploads</h2>';
    if (is_dir('public/uploads')) {
        if (is_writable('public/uploads')) {
            echo '<p class="ok">✅ public/uploads es escribible</p>';
        } else {
            echo '<p class="warning">⚠️ public/uploads no es escribible (chmod 755 o 777)</p>';
            $advertencias[] = 'uploads';
        }
    } else {
        echo '<p class="error">❌ public/uploads no existe</p>';
        $errores[] = 'uploads';
    }
    echo '</div>';
    
    // 6. Verificar conexión a base de datos
    echo '<div class="section">';
    echo '<h2>6. Conexión a Base de Datos</h2>';
    try {
        require_once __DIR__ . '/config/autoload.php';
        $pdo = getDBConnection();
        echo '<p class="ok">✅ Conexión a base de datos exitosa</p>';
        
        // Verificar tablas
        $tablas = ['roles', 'usuarios', 'carpetas_fisicas', 'documentos_auditoria'];
        foreach ($tablas as $tabla) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
            if ($stmt->rowCount() > 0) {
                echo '<p class="ok">✅ Tabla ' . $tabla . ' existe</p>';
            } else {
                echo '<p class="error">❌ Tabla ' . $tabla . ' no existe</p>';
                $errores[] = $tabla;
            }
        }
        
        // Verificar usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        if ($result['total'] > 0) {
            echo '<p class="ok">✅ Hay ' . $result['total'] . ' usuario(s) en la base de datos</p>';
        } else {
            echo '<p class="warning">⚠️ No hay usuarios en la base de datos (crear administrador)</p>';
            $advertencias[] = 'usuarios';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">❌ Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
        $errores[] = 'BD';
    }
    echo '</div>';
    
    // 7. Verificar configuración de seguridad
    echo '<div class="section">';
    echo '<h2>7. Configuración de Seguridad</h2>';
    
    // Verificar .htaccess
    if (file_exists('.htaccess')) {
        $htaccess = file_get_contents('.htaccess');
        if (strpos($htaccess, 'config/') !== false) {
            echo '<p class="ok">✅ .htaccess protege directorio config</p>';
        } else {
            echo '<p class="warning">⚠️ .htaccess puede no proteger todos los directorios</p>';
        }
    }
    
    // Verificar que crear_admin.php no exista o esté protegido
    if (file_exists('database/crear_admin.php')) {
        echo '<p class="warning">⚠️ database/crear_admin.php existe (eliminar después de crear admin)</p>';
        $advertencias[] = 'crear_admin';
    }
    
    echo '</div>';
    
    // Resumen
    echo '<div class="section" style="background-color: #f0f0f0;">';
    echo '<h2>📊 Resumen</h2>';
    echo '<p><strong>Éxitos:</strong> ' . count($exitos) . '</p>';
    echo '<p><strong>Advertencias:</strong> ' . count($advertencias) . '</p>';
    echo '<p><strong>Errores:</strong> ' . count($errores) . '</p>';
    
    if (empty($errores)) {
        echo '<p style="color: green; font-size: 18px; font-weight: bold;">✅ Instalación correcta. Sistema listo para usar.</p>';
    } else {
        echo '<p style="color: red; font-size: 18px; font-weight: bold;">❌ Hay errores que deben corregirse antes de usar el sistema.</p>';
    }
    echo '</div>';
    
    // Advertencia de seguridad
    echo '<div class="section" style="background-color: #fff3cd; border-color: #ffc107;">';
    echo '<h2>🔒 Seguridad</h2>';
    echo '<p><strong>IMPORTANTE:</strong> Elimina este archivo (VERIFICACION_INSTALACION.php) después de verificar la instalación.</p>';
    echo '</div>';
    ?>
    
</body>
</html>

