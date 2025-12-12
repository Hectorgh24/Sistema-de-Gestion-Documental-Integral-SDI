<?php
/**
 * Script para crear usuario administrador inicial
 * EJECUTAR UNA SOLA VEZ después de crear la base de datos
 * 
 * IMPORTANTE: Eliminar este archivo después de crear el admin
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../models/Usuario.php';

// Solo permitir ejecución si no hay usuarios en la BD (medida de seguridad)
$usuarioModel = new Usuario();
$totalUsuarios = $usuarioModel->contar();

if ($totalUsuarios > 0) {
    die('Error: Ya existen usuarios en la base de datos. Este script solo debe ejecutarse una vez.');
}

// Datos del administrador (CAMBIAR ESTOS VALORES)
$datosAdmin = [
    'nombre_completo' => 'Administrador del Sistema',
    'email' => 'admin@sdi.local', // CAMBIAR
    'password' => 'Admin123!', // CAMBIAR - Debe ser una contraseña segura
    'id_rol' => 1 // ID del rol Administrador (según el INSERT en schema.sql)
];

// Validar que la contraseña sea segura
if (strlen($datosAdmin['password']) < 8) {
    die('Error: La contraseña debe tener al menos 8 caracteres.');
}

// Crear el usuario
$idUsuario = $usuarioModel->crear($datosAdmin);

if ($idUsuario) {
    echo "✅ Usuario administrador creado exitosamente!\n";
    echo "ID: $idUsuario\n";
    echo "Email: {$datosAdmin['email']}\n";
    echo "\n";
    echo "⚠️  IMPORTANTE:\n";
    echo "1. Cambia la contraseña después del primer login\n";
    echo "2. ELIMINA este archivo (crear_admin.php) por seguridad\n";
} else {
    echo "❌ Error al crear el usuario administrador.\n";
    echo "Verifica los logs de error para más detalles.\n";
}

