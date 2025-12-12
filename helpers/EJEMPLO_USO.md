# Ejemplos de Uso - Helpers de Seguridad

## Sanitización de Entrada (Input)

```php
<?php
require_once '../config/autoload.php';

// Obtener y sanitizar datos de POST
$email = getPost('email');
$nombre = getPost('nombre');

// Obtener y sanitizar datos de GET
$id = getGet('id', 0);

// Validar email
$emailValido = validateEmail($email);
if (!$emailValido) {
    die('Email inválido');
}

// Validar número entero
$idValido = validateInt($id, 1, 1000);
if (!$idValido) {
    die('ID inválido');
}
```

## Escape de Salida (Output - XSS Protection)

```php
<?php
// SIEMPRE usar escapeOutput() al mostrar datos en HTML
$nombreUsuario = escapeOutput($usuario['nombre_completo']);
$descripcion = escapeOutput($documento['descripcion']);
?>

<div>
    <h1>Bienvenido, <?php echo $nombreUsuario; ?></h1>
    <p><?php echo $descripcion; ?></p>
</div>
```

## Consultas SQL Seguras (Prepared Statements)

```php
<?php
require_once '../config/autoload.php';

// ✅ CORRECTO: Usar prepared statements
$sql = "SELECT * FROM usuarios WHERE email = :email AND estado = :estado";
$params = [
    'email' => $email,
    'estado' => 'activo'
];
$stmt = executeQuery($sql, $params);
$usuario = $stmt->fetch();

// ❌ INCORRECTO: NUNCA concatenar variables directamente
// $sql = "SELECT * FROM usuarios WHERE email = '$email'"; // VULNERABLE A SQL INJECTION
```

## Validación de Archivos

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $archivoValidado = validateUploadedFile(
        $_FILES['archivo'],
        ['pdf', 'docx'],
        5242880 // 5MB
    );
    
    if ($archivoValidado) {
        $nombreUnico = generateUniqueFileName($archivoValidado['name']);
        $rutaDestino = UPLOAD_DIR . $nombreUnico;
        
        if (move_uploaded_file($archivoValidado['tmp_name'], $rutaDestino)) {
            echo 'Archivo subido correctamente';
        }
    } else {
        echo 'Error: Archivo inválido';
    }
}
```

## Gestión de Sesiones

```php
<?php
require_once '../config/autoload.php';

// Verificar autenticación
if (isAuthenticated()) {
    echo 'Usuario autenticado';
}

// Requerir autenticación (redirige si no está autenticado)
requireAuth();

// Requerir rol específico
requireRole('Administrador');
// O múltiples roles
requireRole(['Administrador', 'Academico']);
```

