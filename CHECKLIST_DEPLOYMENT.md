# ✅ Checklist de Deployment - SDI Gestión Documental

Este documento contiene todos los pasos necesarios para tener el sistema completamente funcional.

## 🔧 Pre-Requisitos

- [ ] XAMPP/Apache instalado con mod_rewrite habilitado
- [ ] MySQL/MariaDB corriendo
- [ ] PHP 7.4 o superior
- [ ] phpMyAdmin o acceso directo a MySQL

## 📦 Instalación de Base de Datos

### Paso 1: Crear Base de Datos
```sql
CREATE DATABASE sdi_gestion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 2: Importar Schema
```bash
mysql -u root -p sdi_gestion < database/schema.sql
```

O mediante phpMyAdmin:
1. Selecciona base de datos `sdi_gestion`
2. Vá a "Importar"
3. Selecciona archivo `database/schema.sql`
4. Click en "Ejecutar"

## ⚙️ Configuración de Aplicación

### Paso 1: Verificar config/db.php
```bash
c:\xampp\htdocs\Programa-Gestion-SDI\config\db.php
```

Asegúrate que tenga:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sdi_gestion');
define('DB_USER', 'root');
define('DB_PASS', 'password'); // Cambiar si es necesario
```

### Paso 2: Verificar config/constants.php
El archivo debe estar en lugar y contener constantes de aplicación.

### Paso 3: Verificar Apache mod_rewrite
```bash
# En cmd/PowerShell (como Administrador)
cd C:\xampp\apache\bin
httpd -M | findstr rewrite
```

Debe mostrar: `rewrite_module (shared)`

Si no aparece, habilitar en `httpd.conf`:
- Buscar: `#LoadModule rewrite_module modules/mod_rewrite.so`
- Descomentar (quitar #)
- Reiniciar Apache

## 🚀 Inicialización del Sistema

### Paso 1: Crear Usuarios de Prueba
Abrir en navegador:
```
http://localhost/Programa-Gestion-SDI/crear_admin_test.php
```

Esto creará:
- **admin@sdi.local** / admin123 (Administrador)
- **administrativo@sdi.local** / admin123 (Personal Administrativo)
- **estudiante@sdi.local** / admin123 (Estudiante SS)

### Paso 2: Verificar Instalación
Abrir en navegador:
```
http://localhost/Programa-Gestion-SDI/VERIFICACION_COMPLETA.php
```

Debe mostrar "Sistema completamente operativo" en verde.

### Paso 3: Acceder a la Aplicación
```
http://localhost/Programa-Gestion-SDI/index.html
```

Usar credenciales de admin.

## 📋 Checklist de Verificación

### Archivos Requeridos
- [ ] `/config/db.php` existe y tiene credenciales correctas
- [ ] `/config/constants.php` existe
- [ ] `/config/autoload.php` existe
- [ ] `/router.php` existe
- [ ] `/index.html` existe
- [ ] `/login.html` existe
- [ ] `/app/models/*.php` existen
- [ ] `/app/controllers/*.php` existen
- [ ] `/public/js/*.js` existen

### Directorios Requeridos
- [ ] `/app` existe
- [ ] `/app/models` existe
- [ ] `/app/controllers` existe
- [ ] `/app/middleware` existe
- [ ] `/config` existe
- [ ] `/database` existe
- [ ] `/public` existe
- [ ] `/public/js` existe
- [ ] `/public/css` existe
- [ ] `/public/uploads` existe y tiene permisos 777

### Base de Datos
- [ ] Base de datos `sdi_gestion` creada
- [ ] Tabla `roles` tiene 3 registros
- [ ] Tabla `usuarios` tiene al menos 1 usuario
- [ ] Usuario admin está activo

### Funcionalidad
- [ ] Login funciona con admin@sdi.local
- [ ] Dashboard carga sin errores
- [ ] Menú muestra opciones según rol
- [ ] test_api.html muestra endpoints funcionales
- [ ] Cambio de contraseña funciona

## 🔐 Seguridad

### Cambios Recomendados ANTES de Production

1. **Cambiar contraseñas de usuarios**
   - Login como admin
   - Ir a "Mi Perfil"
   - Cambiar contraseña

2. **Crear nuevo usuario administrador**
   - Como admin, crear nuevo usuario
   - Asignarle rol Administrador
   - Usar ese usuario como administrador principal

3. **Eliminar usuarios de prueba**
   - Como admin, cambiar estado a "inactivo"
   - Luego eliminar si es necesario

4. **Desactivar scripts de prueba**
   - Renombrar o eliminar `crear_admin_test.php`
   - Renombrar o eliminar `test_api.html`
   - Renombrar o eliminar `VERIFICACION_COMPLETA.php`

5. **Configurar variables de entorno**
   - Crear `.env.local` (no versionar)
   - Mover credenciales de BD a variables de entorno
   - Usar en `config/db.php`

6. **Revisar .htaccess**
   - Asegurar que bloquea acceso a `/app`, `/config`, `/database`
   - Verificar headers de seguridad

## 📱 Testing

### Test Manual Básico

1. **Login**
   - Abrir `login.html`
   - Usar: admin@sdi.local / admin123
   - Debe entrar a dashboard

2. **Dashboard**
   - Debe mostrar estadísticas
   - Debe mostrar menús según rol

3. **Usuarios** (solo Admin)
   - Click en "Usuarios"
   - Debe mostrar lista de usuarios
   - Crear nuevo usuario
   - Editar usuario
   - Cambiar estado

4. **Documentos**
   - Click en "Documentos"
   - Debe mostrar lista de documentos (vacía al inicio)
   - Crear nuevo documento
   - Debe pedir Categoría y Carpeta

5. **Mi Perfil**
   - Click en "Mi Perfil"
   - Cambiar contraseña
   - Logout
   - Login con nueva contraseña

### Test API Avanzado

Abrir `test_api.html`:
1. Click en "Test Login" - Debe devolver token
2. Click en "Test de Verificar Sesión" - Debe devolver datos usuario
3. Click en "Test Listar Usuarios" - Debe devolver lista
4. Click en "Test Listar Documentos" - Debe devolver lista vacía
5. Click en "Test Dashboard" - Debe devolver estadísticas
6. Click en "Cerrar Sesión" - Debe limpiar sesión

## 🐛 Troubleshooting

### "Página no encontrada" (404)
```
Solución: Asegurar que está en /Programa-Gestion-SDI/
          Revisar que Apache mod_rewrite está habilitado
          Revisar .htaccess está presente
```

### "Error de conexión a BD"
```
Solución: Verificar credentials en config/db.php
          Asegurar MySQL está corriendo
          Verificar que base de datos existe
          Revisar permisos del usuario MySQL
```

### "Error 500"
```
Solución: Ver error_log de Apache
          Ejecutar VERIFICACION_COMPLETA.php
          Revisar que todos los archivos existen
          Revisar que PDO está instalado
```

### "No puedo logearme"
```
Solución: Ejecutar crear_admin_test.php
          Verificar usuario existe en BD
          Verificar usuario está activo (estado='activo')
          Revisar que Password está hasheado con bcrypt
```

### "Menús no aparecen según rol"
```
Solución: Logout e login nuevamente
          Abrir DevTools (F12) console
          Ejecutar: console.log(auth.usuario)
          Verificar que 'rol' coincide con rol en BD
```

## 📊 Ejemplo de Flujo Completo

1. Admin crea nueva Categoría: "Facturas"
   - Agrega campos: Número, Monto, Vencimiento, Pagada

2. Admin crea nueva Carpeta: "Contabilidad 2024"

3. Admin crea documentos ejemplo (para pruebas)

4. Admin crea usuario Administrativo: "contador@sdi.local"

5. Contador login y ve módulos de gestión
   - Puede crear/editar documentos pero NO usuarios

6. Admin crea usuario Estudiante: "estudiante@sdi.local"

7. Estudiante login y solo ve sus documentos
   - Puede crear documentos pero NO ver documentos de otros
   - NO puede gestionar categorías, carpetas, usuarios

## 📈 Performance y Optimización

### Base de Datos
- [ ] Índices creados en columnas de búsqueda
- [ ] Columnas correctas en tablas
- [ ] Relaciones foráneas definidas

### PHP/Apache
- [ ] PHP Opcache habilitado (en producción)
- [ ] Gzip compression habilitado
- [ ] Caché del navegador configurado
- [ ] Session handlers optimizados

### Frontend
- [ ] CSS viene de CDN (Tailwind)
- [ ] Iconos vienen de CDN (Font Awesome)
- [ ] JavaScript minimizado en producción

## 🔄 Tareas de Mantenimiento

### Semanal
- [ ] Revisar logs de Apache/PHP
- [ ] Verificar que backup de BD está funcionando
- [ ] Revisar usuarios inactivos

### Mensual
- [ ] Actualizar contraseñas de usuarios administrativos
- [ ] Revisar documentos marcados como cancelados
- [ ] Limpiar archivos temporales de uploads
- [ ] Verificar uso de almacenamiento

### Anual
- [ ] Revisar y actualizar políticas de seguridad
- [ ] Capacitar usuarios en nuevas funcionalidades
- [ ] Planificar mejoras y nuevas funcionalidades

## 📞 Contacto y Soporte

Si después de seguir este checklist siguen habiendo problemas:

1. Revisar DOCUMENTACION_TECNICA.md
2. Revisar GUIA_RAPIDA.md
3. Ejecutar VERIFICACION_COMPLETA.php
4. Revisar error_log de Apache en `C:\xampp\apache\logs\`
5. Revisar console del navegador (F12 > Console)

---

**Última actualización:** Enero 2024
**Estado:** Production-Ready
**Versión:** 2.0.0
