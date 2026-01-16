<?php
/**
 * Script de Verificación de Instalación - SDI Gestión Documental
 * 
 * Verifica que todos los componentes necesarios estén presentes y funcionales.
 */

$checks = [
    'php_version' => ['label' => 'PHP Version >= 7.4', 'pass' => version_compare(PHP_VERSION, '7.4.0', '>=')],
    'pdo_extension' => ['label' => 'PDO Extension', 'pass' => extension_loaded('pdo')],
    'pdo_mysql' => ['label' => 'PDO MySQL Driver', 'pass' => extension_loaded('pdo_mysql')],
    'sessions' => ['label' => 'Session Support', 'pass' => function_exists('session_start')],
    'json' => ['label' => 'JSON Support', 'pass' => function_exists('json_encode')],
    'bcrypt' => ['label' => 'Bcrypt Support', 'pass' => function_exists('password_hash')],
];

$files = [
    'config/db.php' => 'config/db.php',
    'config/constants.php' => 'config/constants.php',
    'config/autoload.php' => 'config/autoload.php',
    'router.php' => 'router.php',
    'index.html' => 'index.html',
    'login.html' => 'login.html',
    'models/Usuario.php' => 'models/Usuario.php',
    'controllers/AuthController.php' => 'controllers/AuthController.php',
    'public/js/api.js' => 'public/js/api.js',
    'public/js/auth.js' => 'public/js/auth.js',
];

$directories = [
    'models' => 'models',
    'controllers' => 'controllers',
    'middleware' => 'middleware',
    'config' => 'config',
    'database' => 'database',
    'public' => 'public',
    'public/js' => 'public/js',
    'public/css' => 'public/css',
    'public/uploads' => 'public/uploads',
];

$db_check = false;
$db_error = '';

// Intentar conectar a BD
try {
    require_once 'config/db.php';
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $db_check = true;
} catch (Exception $e) {
    $db_check = false;
    $db_error = $e->getMessage();
}

// Verificar tabla usuarios
$usuarios_table = false;
if ($db_check) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM usuarios LIMIT 1");
        $usuarios_table = true;
    } catch (Exception $e) {
        $usuarios_table = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Instalación - SDI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">SDI - Verificación de Instalación</h1>
                <p class="text-gray-600">Reporte de diagnóstico del sistema</p>
            </div>

            <!-- Información del Sistema -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-server text-blue-500 mr-2"></i>Información del Sistema
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <p class="text-gray-600 text-sm">Versión PHP</p>
                        <p class="text-lg font-semibold"><?php echo phpversion(); ?></p>
                    </div>
                    <div class="border-l-4 border-green-500 pl-4">
                        <p class="text-gray-600 text-sm">SAPI</p>
                        <p class="text-lg font-semibold"><?php echo php_sapi_name(); ?></p>
                    </div>
                    <div class="border-l-4 border-purple-500 pl-4">
                        <p class="text-gray-600 text-sm">Sistema Operativo</p>
                        <p class="text-lg font-semibold"><?php echo php_uname('s'); ?></p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-4">
                        <p class="text-gray-600 text-sm">Ruta de Directorio</p>
                        <p class="text-lg font-semibold text-sm"><?php echo __DIR__; ?></p>
                    </div>
                </div>
            </div>

            <!-- Requisitos de PHP -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>Requisitos de PHP
                </h2>
                <div class="space-y-3">
                    <?php foreach ($checks as $key => $check): ?>
                        <div class="flex items-center justify-between p-3 <?php echo $check['pass'] ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                            <span class="font-medium text-gray-800"><?php echo $check['label']; ?></span>
                            <span class="font-bold <?php echo $check['pass'] ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $check['pass'] ? '<i class="fas fa-check"></i> OK' : '<i class="fas fa-times"></i> FALLA'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Archivos Requeridos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-files text-blue-500 mr-2"></i>Archivos Requeridos
                </h2>
                <div class="space-y-2">
                    <?php foreach ($files as $name => $path): ?>
                        <?php $exists = file_exists($path); ?>
                        <div class="flex items-center justify-between p-3 <?php echo $exists ? 'bg-green-50' : 'bg-red-50'; ?> border-l-4 <?php echo $exists ? 'border-green-500' : 'border-red-500'; ?>">
                            <span class="font-medium text-gray-800"><?php echo $name; ?></span>
                            <span class="font-bold <?php echo $exists ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $exists ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Directorios Requeridos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-folder text-purple-500 mr-2"></i>Directorios Requeridos
                </h2>
                <div class="space-y-2">
                    <?php foreach ($directories as $name => $path): ?>
                        <?php $exists = is_dir($path); ?>
                        <div class="flex items-center justify-between p-3 <?php echo $exists ? 'bg-green-50' : 'bg-red-50'; ?> border-l-4 <?php echo $exists ? 'border-green-500' : 'border-red-500'; ?>">
                            <span class="font-medium text-gray-800"><?php echo $name; ?></span>
                            <span class="font-bold <?php echo $exists ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $exists ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Base de Datos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-database text-orange-500 mr-2"></i>Verificación de Base de Datos
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 <?php echo $db_check ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                        <span class="font-medium text-gray-800">Conexión a BD</span>
                        <span class="font-bold <?php echo $db_check ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $db_check ? '<i class="fas fa-check"></i> Conectada' : '<i class="fas fa-times"></i> Error'; ?>
                        </span>
                    </div>
                    
                    <?php if (!$db_check): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <p class="font-semibold mb-2">Error de Conexión:</p>
                            <code class="text-sm"><?php echo htmlspecialchars($db_error); ?></code>
                        </div>
                    <?php endif; ?>

                    <?php if ($db_check): ?>
                        <div class="flex items-center justify-between p-3 <?php echo $usuarios_table ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                            <span class="font-medium text-gray-800">Tabla 'usuarios' existe</span>
                            <span class="font-bold <?php echo $usuarios_table ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $usuarios_table ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                            </span>
                        </div>

                        <?php if ($usuarios_table): ?>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $user_count = $result['total'];
                            ?>
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-3">
                                <p class="font-medium text-gray-800">Usuarios en BD: <span class="text-blue-600"><?php echo $user_count; ?></span></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estado General -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-heartbeat text-red-500 mr-2"></i>Estado General
                </h2>
                <?php
                $all_checks_pass = array_reduce($checks, function($carry, $item) { 
                    return $carry && $item['pass']; 
                }, true);
                $all_files_exist = array_reduce($files, function($carry, $path) {
                    return $carry && file_exists($path);
                }, true);
                $all_dirs_exist = array_reduce($directories, function($carry, $path) {
                    return $carry && is_dir($path);
                }, true);
                $system_ok = $all_checks_pass && $all_files_exist && $all_dirs_exist && $db_check && $usuarios_table;
                ?>
                <div class="<?php echo $system_ok ? 'bg-green-50 border-l-4 border-green-500' : 'bg-yellow-50 border-l-4 border-yellow-500'; ?> p-6 rounded">
                    <p class="text-lg font-semibold mb-2">
                        <?php if ($system_ok): ?>
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span class="text-green-700">Sistema completamente operativo</span>
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-yellow-700">Revisar problemas encontrados</span>
                        <?php endif; ?>
                    </p>
                    <p class="text-gray-700 text-sm">
                        <?php if ($system_ok): ?>
                            ✓ Todos los requisitos están satisfechos<br>
                            ✓ Todos los archivos están presentes<br>
                            ✓ Base de datos está configurada y accesible<br>
                            <strong>¡Puedes proceder a usar la aplicación!</strong>
                        <?php else: ?>
                            Por favor, soluciona los problemas marcados en rojo antes de usar el sistema.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Botones de Navegación -->
            <div class="space-y-3">
                <?php if ($system_ok): ?>
                    <a href="crear_admin_test.php" class="block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg text-center">
                        <i class="fas fa-user-plus mr-2"></i>Crear Usuarios de Prueba
                    </a>
                    <a href="login.html" class="block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg text-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Ir a Login
                    </a>
                    <a href="test_api.html" class="block bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 px-6 rounded-lg text-center">
                        <i class="fas fa-flask mr-2"></i>Probar Endpoints
                    </a>
                <?php else: ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg">
                        <p class="font-bold mb-2">⚠️ No se puede proceder</p>
                        <p class="text-sm">Por favor, soluciona los problemas encontrados arriba antes de continuar.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="text-center mt-12 text-gray-600 text-sm">
                <p>SDI - Gestión Documental | Verificación: <?php echo date('d/m/Y H:i:s'); ?></p>
                <p class="mt-2">Para más información, consulta DOCUMENTACION_TECNICA.md o GUIA_RAPIDA.md</p>
            </div>
        </div>
    </div>
</body>
</html>
