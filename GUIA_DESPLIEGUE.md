# 🚀 Guía de Despliegue - SDI Gestión Documental

## ✅ Checklist Pre-Despliegue

### 1. Verificación de Archivos
- [x] Estructura de carpetas completa
- [x] Archivos de configuración presentes
- [x] Controladores y modelos creados
- [x] Vistas implementadas
- [x] Helpers de seguridad incluidos

### 2. Configuración Requerida

#### Antes de subir al servidor:

1. **Editar `config/db.php`** con las credenciales de InfinityFree:
   ```php
   define('DB_HOST', 'localhost'); // O el host que proporcione InfinityFree
   define('DB_NAME', 'SDI_Gestion_Documental');
   define('DB_USER', 'tu_usuario_infinityfree');
   define('DB_PASS', 'tu_password_infinityfree');
   ```

2. **Verificar permisos del directorio `public/uploads/`**:
   - Debe tener permisos 755 o 777 (según InfinityFree)
   - Debe ser escribible por el servidor web

---

## 📋 Pasos de Despliegue

### Paso 1: Preparar Archivos

1. **Comprimir el proyecto** (opcional, pero recomendado):
   ```bash
   # Excluir archivos innecesarios
   - Requerimientos y logica de negocio-SDI.pdf (no necesario en producción)
   - helpers/EJEMPLO_USO.md (documentación, opcional)
   ```

2. **Verificar que `.htaccess` esté incluido** (importante para seguridad)

### Paso 2: Subir al Servidor InfinityFree

1. **Acceder al File Manager de InfinityFree** (o usar FTP)

2. **Subir todos los archivos** a la carpeta `htdocs` o `public_html`:
   ```
   /htdocs/
   ├── config/
   ├── controllers/
   ├── models/
   ├── views/
   ├── helpers/
   ├── public/
   ├── database/
   ├── .htaccess
   ├── index.php
   ├── login.php
   └── dashboard.php
   ```

3. **Verificar permisos**:
   - Directorios: 755
   - Archivos PHP: 644
   - `public/uploads/`: 755 o 777 (según requiera InfinityFree)

### Paso 3: Crear Base de Datos

1. **Acceder a phpMyAdmin** desde el panel de InfinityFree

2. **Crear la base de datos**:
   - Nombre: `SDI_Gestion_Documental`
   - Collation: `utf8mb4_unicode_ci`

3. **Importar el esquema**:
   - Seleccionar la base de datos creada
   - Ir a la pestaña "Importar"
   - Seleccionar el archivo `database/schema.sql`
   - Clic en "Continuar"

   **O copiar y pegar** el contenido de `database/schema.sql` en la pestaña "SQL"

### Paso 4: Configurar Conexión a Base de Datos

1. **Editar `config/db.php`** en el servidor:
   - Usar las credenciales que InfinityFree proporcionó
   - **IMPORTANTE**: El `DB_HOST` puede ser diferente a `localhost`
     - Verificar en el panel de InfinityFree el host correcto
     - Puede ser: `sqlXXX.epizy.com` o similar

2. **Verificar la conexión**:
   - Crear un archivo temporal `test_db.php`:
   ```php
   <?php
   require_once 'config/autoload.php';
   try {
       $pdo = getDBConnection();
       echo "✅ Conexión exitosa a la base de datos";
   } catch (Exception $e) {
       echo "❌ Error: " . $e->getMessage();
   }
   ?>
   ```
   - Acceder a `https://tu-dominio.com/test_db.php`
   - **Eliminar este archivo después de verificar**

### Paso 5: Crear Usuario Administrador

**Opción A: Usando el script (Recomendado)**

1. **Editar `database/crear_admin.php`** en el servidor:
   ```php
   $datosAdmin = [
       'nombre_completo' => 'Tu Nombre',
       'email' => 'tu_email@ejemplo.com', // CAMBIAR
       'password' => 'TuPasswordSeguro123!', // CAMBIAR
       'id_rol' => 1
   ];
   ```

2. **Temporalmente desproteger el directorio database**:
   - Comentar las líneas 37-44 en `.htaccess`:
   ```apache
   # <DirectoryMatch "^.*/database/">
   #     <FilesMatch "^(?!crear_admin\.php).*$">
   #         Order Allow,Deny
   #         Deny from all
   #     </FilesMatch>
   # </DirectoryMatch>
   ```

3. **Acceder a**: `https://tu-dominio.com/database/crear_admin.php`

4. **Verificar que se creó el usuario**:
   - Deberías ver: "✅ Usuario administrador creado exitosamente!"

5. **Restaurar protección y eliminar el script**:
   - Descomentar las líneas en `.htaccess`
   - **ELIMINAR** `database/crear_admin.php` por seguridad

**Opción B: Manualmente con SQL**

1. **Generar hash de contraseña**:
   - Crear archivo temporal `hash_password.php`:
   ```php
   <?php
   echo password_hash('TuPasswordSeguro123!', PASSWORD_DEFAULT);
   ?>
   ```
   - Acceder y copiar el hash generado
   - Eliminar el archivo

2. **Ejecutar en phpMyAdmin**:
   ```sql
   INSERT INTO usuarios (nombre_completo, email, password_hash, id_rol, estado)
   VALUES (
       'Administrador del Sistema',
       'tu_email@ejemplo.com',
       'PEGAR_AQUI_EL_HASH_GENERADO',
       1,
       'activo'
   );
   ```

### Paso 6: Verificar Instalación

1. **Acceder al login**:
   - URL: `https://tu-dominio.com/login.php`
   - O simplemente: `https://tu-dominio.com/`

2. **Iniciar sesión** con las credenciales del administrador

3. **Verificar el dashboard**:
   - Deberías ver las estadísticas y módulos según tu rol
   - Probar el modo oscuro/claro
   - Probar el botón de accesibilidad

### Paso 7: Configuración de Producción

1. **Verificar que los errores estén desactivados**:
   - En `config/autoload.php`, línea 45, verificar:
   ```php
   if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
       ini_set('display_errors', '0');
   }
   ```

2. **Definir entorno de producción** (opcional):
   - Agregar al inicio de `index.php`:
   ```php
   define('ENVIRONMENT', 'production');
   ```

3. **Verificar permisos de `public/uploads/`**:
   ```bash
   chmod 755 public/uploads
   # O si es necesario:
   chmod 777 public/uploads
   ```

---

## 🔒 Seguridad Post-Despliegue

### Archivos a Eliminar/Proteger:

- [ ] **Eliminar** `database/crear_admin.php` (después de crear admin)
- [ ] **Eliminar** cualquier archivo de prueba (`test_db.php`, `hash_password.php`)
- [ ] **Verificar** que `.htaccess` esté protegiendo directorios sensibles
- [ ] **Verificar** que `config/db.php` no sea accesible públicamente

### Verificaciones de Seguridad:

1. **Probar acceso directo a archivos protegidos**:
   - Intentar acceder a: `https://tu-dominio.com/config/db.php`
   - Debería mostrar error 403 o página en blanco

2. **Verificar headers de seguridad**:
   - Usar herramienta como: https://securityheaders.com
   - Verificar que los headers estén configurados

---

## 🐛 Solución de Problemas Comunes

### Error: "Error de conexión a la base de datos"

**Causas posibles:**
- Credenciales incorrectas en `config/db.php`
- `DB_HOST` incorrecto (no siempre es `localhost` en InfinityFree)
- Base de datos no creada

**Solución:**
1. Verificar credenciales en el panel de InfinityFree
2. Verificar el host correcto de la base de datos
3. Verificar que la base de datos existe

### Error: "Página en blanco"

**Causas posibles:**
- Error de PHP (verificar logs)
- Permisos incorrectos
- Archivo faltante

**Solución:**
1. Activar temporalmente `display_errors` en `config/autoload.php`
2. Verificar logs de error de InfinityFree
3. Verificar que todos los archivos estén subidos

### Error: "No se pueden subir archivos"

**Causas posibles:**
- Permisos incorrectos en `public/uploads/`
- Límite de tamaño en PHP

**Solución:**
1. Verificar permisos: `chmod 755 public/uploads` o `chmod 777`
2. Verificar configuración PHP en `.htaccess`

### Error: "Session cannot be started"

**Causas posibles:**
- Permisos de directorio de sesiones
- Configuración de PHP

**Solución:**
1. Verificar permisos de directorios
2. Contactar soporte de InfinityFree si persiste

---

## 📞 Información de Contacto y Soporte

### Recursos de InfinityFree:
- Panel de Control: https://infinityfree.net/
- Documentación: https://forum.infinityfree.com/
- Soporte: A través del foro

### Información del Sistema:
- **Versión PHP requerida**: 8.x
- **Versión MySQL requerida**: 5.7+
- **Espacio requerido**: ~5MB (sin archivos subidos)

---

## ✅ Checklist Final Post-Despliegue

- [ ] Base de datos creada e importada
- [ ] Credenciales de BD configuradas correctamente
- [ ] Usuario administrador creado
- [ ] Login funciona correctamente
- [ ] Dashboard se muestra según el rol
- [ ] Modo oscuro/claro funciona
- [ ] Botón de accesibilidad funciona
- [ ] Directorio `public/uploads/` tiene permisos correctos
- [ ] Archivos de prueba eliminados
- [ ] `.htaccess` protege directorios sensibles
- [ ] Errores de PHP desactivados en producción
- [ ] Sistema probado y funcionando

---

## 🎉 ¡Despliegue Completado!

Una vez completados todos los pasos, tu sistema SDI Gestión Documental estará listo para usar.

**Próximos pasos sugeridos:**
- Crear usuarios adicionales según roles
- Configurar carpetas físicas
- Comenzar a cargar documentos

---

**Nota importante**: Guarda una copia de seguridad de `config/db.php` con las credenciales en un lugar seguro.

