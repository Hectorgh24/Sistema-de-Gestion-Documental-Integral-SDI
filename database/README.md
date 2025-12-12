# Base de Datos - SDI Gestión Documental

## Instalación

### 1. Ejecutar el Script SQL

Ejecuta el archivo `schema.sql` en tu base de datos MySQL de InfinityFree:

```sql
-- Copia y pega el contenido de schema.sql en phpMyAdmin o tu cliente MySQL
```

### 2. Crear Usuario Administrador

**Opción A: Usando el script PHP (Recomendado para primera vez)**

1. Edita `crear_admin.php` y cambia:
   - `email`: Tu email de administrador
   - `password`: Una contraseña segura (mínimo 8 caracteres)

2. Accede a: `https://tu-dominio.com/database/crear_admin.php`

3. **IMPORTANTE**: Elimina el archivo `crear_admin.php` después de crear el admin

**Opción B: Manualmente con SQL**

```sql
-- Reemplaza 'tu_email@ejemplo.com' y genera el hash de la contraseña en PHP
INSERT INTO usuarios (nombre_completo, email, password_hash, id_rol, estado)
VALUES (
    'Administrador del Sistema',
    'tu_email@ejemplo.com',
    '$2y$10$...', -- Generar con: password_hash('tu_password', PASSWORD_DEFAULT)
    1, -- ID del rol Administrador
    'activo'
);
```

### 3. Generar Hash de Contraseña (Para Opción B)

Si necesitas generar el hash manualmente, usa este código PHP:

```php
<?php
echo password_hash('tu_contraseña_segura', PASSWORD_DEFAULT);
?>
```

## Estructura de Tablas

- `roles`: Roles del sistema (Administrador, Academico, Alumno)
- `usuarios`: Usuarios del sistema
- `carpetas_fisicas`: Carpetas físicas de documentos
- `documentos_auditoria`: Documentos de auditoría

## Notas de Seguridad

- ✅ Todas las contraseñas se almacenan con `password_hash()` (bcrypt)
- ✅ Las consultas usan Prepared Statements (protección SQL Injection)
- ✅ Los datos se sanitizan antes de insertar (protección XSS)

