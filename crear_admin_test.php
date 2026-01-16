<?php
/**
 * Script para crear usuario administrador de prueba
 * 
 * Este archivo debe ejecutarse UNA sola vez después de que schema.sql
 * ha sido importado. Luego puede ser eliminado o renombrado.
 * 
 * Acceso: http://localhost/Programa-Gestion-SDI/crear_admin_test.php
 */

// Cargar configuración
require_once 'config/db.php';

$created = false;
$message = '';

try {
    // Conectar a BD
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@sdi.local']);
    
    if ($stmt->rowCount() > 0) {
        $message = '✓ Usuario admin@sdi.local ya existe en la base de datos';
    } else {
        // Crear usuario admin
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (email, nombre, apellido_paterno, apellido_materno, password, id_rol, estado, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            'admin@sdi.local',
            'Administrador',
            'Sistema',
            'SDI',
            $hashedPassword,
            3,  // ID de rol Administrador
            'activo'
        ]);

        if ($result) {
            $created = true;
            $message = '✓ Usuario administrador creado exitosamente';
        } else {
            $message = '✗ Error al crear usuario';
        }
    }

    // Crear otros usuarios de prueba
    $usuarios_prueba = [
        [
            'email' => 'administrativo@sdi.local',
            'nombre' => 'Administrativo',
            'apellido_paterno' => 'Prueba',
            'password' => 'admin123',
            'id_rol' => 2  // Personal Administrativo
        ],
        [
            'email' => 'estudiante@sdi.local',
            'nombre' => 'Estudiante',
            'apellido_paterno' => 'Prueba',
            'password' => 'admin123',
            'id_rol' => 1  // Estudiante SS
        ]
    ];

    foreach ($usuarios_prueba as $usuario) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$usuario['email']]);
        
        if ($stmt->rowCount() === 0) {
            $hashedPassword = password_hash($usuario['password'], PASSWORD_BCRYPT, ['cost' => 10]);
            
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (email, nombre, apellido_paterno, apellido_materno, password, id_rol, estado, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $usuario['email'],
                $usuario['nombre'],
                $usuario['apellido_paterno'],
                '',
                $hashedPassword,
                $usuario['id_rol'],
                'activo'
            ]);
            
            $message .= "\n✓ Usuario " . $usuario['email'] . " creado";
        } else {
            $message .= "\n✓ Usuario " . $usuario['email'] . " ya existe";
        }
    }

} catch (Exception $e) {
    $message = '✗ Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Admin - SDI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <div class="text-center mb-6">
                <i class="fas fa-check-circle text-4xl <?php echo $created ? 'text-green-500' : 'text-blue-500'; ?> mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800">Usuarios de Prueba</h1>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <pre class="text-sm whitespace-pre-wrap text-gray-700"><?php echo htmlspecialchars($message); ?></pre>
            </div>

            <div class="space-y-4 mb-6">
                <div class="border rounded-lg p-4 bg-blue-50">
                    <h3 class="font-semibold text-blue-900 mb-2">Credenciales de Prueba:</h3>
                    <div class="text-sm space-y-2">
                        <div>
                            <label class="font-medium">Admin:</label>
                            <code class="text-xs bg-white p-1 rounded">admin@sdi.local / admin123</code>
                        </div>
                        <div>
                            <label class="font-medium">Administrativo:</label>
                            <code class="text-xs bg-white p-1 rounded">administrativo@sdi.local / admin123</code>
                        </div>
                        <div>
                            <label class="font-medium">Estudiante:</label>
                            <code class="text-xs bg-white p-1 rounded">estudiante@sdi.local / admin123</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <a href="/Programa-Gestion-SDI/login.html" class="block w-full py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-center font-medium">
                    Ir a Login
                </a>
                <a href="/Programa-Gestion-SDI/test_api.html" class="block w-full py-2 px-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-center font-medium">
                    Test API
                </a>
            </div>

            <p class="text-xs text-gray-500 text-center mt-6">
                Este script puede ser eliminado después de la instalación.
            </p>
        </div>
    </div>
</body>
</html>
