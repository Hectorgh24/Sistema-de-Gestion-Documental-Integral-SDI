# 🚀 Guía Rápida - SDI Gestión Documental

## ⚡ Inicio Rápido (5 minutos)

### 1. Verificar Instalación
Abre en tu navegador:
```
http://localhost/Programa-Gestion-SDI/VERIFICACION_INSTALACION.php
```

### 2. Crear Usuarios de Prueba
Si es la primera vez, ejecuta:
```
http://localhost/Programa-Gestion-SDI/crear_admin_test.php
```

Esto creará 3 usuarios de prueba con diferentes roles.

### 3. Acceder a la Aplicación
```
URL: http://localhost/Programa-Gestion-SDI/index.html
Email: admin@sdi.local
Contraseña: admin123
```

### 4. Probar Endpoints
```
http://localhost/Programa-Gestion-SDI/test_api.html
```

---

## 📋 Estructura de Roles

### 👑 Administrador
- ✅ Gestionar Usuarios (crear, editar, eliminar)
- ✅ Gestionar Documentos (todos)
- ✅ Gestionar Carpetas Físicas
- ✅ Gestionar Categorías
- ✅ Ver Dashboard
- **Acceso total al sistema**

### 👔 Personal Administrativo
- ❌ Gestionar Usuarios (bloqueado)
- ✅ Gestionar Documentos (crear, editar)
- ✅ Gestionar Carpetas Físicas
- ✅ Gestionar Categorías
- ✅ Ver Dashboard
- **Acceso administrativo limitado**

### 📚 Estudiante SS
- ❌ Gestionar Usuarios (bloqueado)
- ✅ Gestionar Documentos **propios** (crear, editar)
- ❌ Gestionar Carpetas (bloqueado)
- ❌ Gestionar Categorías (bloqueado)
- ✅ Ver Dashboard
- **Acceso solo a sus documentos**

---

## 🔐 Cambiar Contraseña

1. Inicia sesión
2. Click en "Mi Perfil" (menú lateral)
3. Completa el formulario de cambio de contraseña
4. Click en "Cambiar Contraseña"

---

## 📊 Dashboard

El dashboard muestra:
- Total de usuarios en el sistema
- Usuarios activos
- Total de documentos
- Documentos pendientes de revisión

---

## 🗂️ Gestionar Documentos

### Crear Documento
1. Click en "Documentos" (menú)
2. Click en "Nuevo Documento"
3. Selecciona Categoría y Carpeta
4. Completa los campos dinámicos
5. Click en "Crear"

### Estados de Documento
- **Pendiente**: Recién creado
- **En Revisión**: Siendo evaluado
- **Archivado**: Completado
- **Cancelado**: Descartado

### Estados de Respaldo
- **Sin Respaldo**: No tiene copia digital
- **Con Respaldo**: Tiene copia digital

---

## 👥 Gestionar Usuarios (Admin Only)

### Crear Usuario
1. Click en "Usuarios" (solo visible para Admin)
2. Click en "Nuevo Usuario"
3. Completa: Nombre, Email, Contraseña
4. Click en "Crear Usuario"

### Cambiar Estado Usuario
1. Selecciona usuario de la lista
2. Cambia estado: Activo → Inactivo → Suspendido
3. Los usuarios inactivos no pueden acceder

### Eliminar Usuario
1. Click en icono de papelera
2. Confirma la eliminación
- ⚠️ No se puede auto-eliminar

---

## 🗂️ Gestionar Carpetas (Admin/Administrativo)

### Crear Carpeta
1. Click en "Carpetas Físicas"
2. Click en "Nueva Carpeta"
3. Ingresa: Etiqueta (único) y descripción
4. Click en "Crear"

### Reglas
- La etiqueta debe ser única
- No se puede eliminar si contiene documentos
- Usar para organizar documentos por ubicación física

---

## 🏷️ Gestionar Categorías (Admin/Administrativo)

### Crear Categoría
1. Click en "Categorías"
2. Click en "Nueva Categoría"
3. Ingresa nombre y descripción
4. (Opcional) Agregá campos dinámicos
5. Click en "Crear"

### Campos Dinámicos
Las categorías pueden tener campos personalizados:
- **Texto Corto**: Línea simple de texto (ej: "Referencia")
- **Texto Largo**: Párrafo de texto (ej: "Descripción")
- **Número Entero**: Cantidad (ej: "Cantidad de páginas")
- **Número Decimal**: Número con decimales (ej: "Monto")
- **Fecha**: Campo de fecha (ej: "Fecha de vencimiento")
- **Booleano**: Sí/No (ej: "¿Confidencial?")

---

## 🔍 Filtrar y Buscar

### Documentos
- Filtrar por Estado Gestión
- Filtrar por Estado Respaldo
- Filtrar por Carpeta
- Filtrar por Categoría
- Rango de fechas

### Usuarios
- Filtrar por Estado (Activo/Inactivo/Suspendido)
- Filtrar por Rol
- Buscar por email o nombre

---

## 📱 Responsividad

La aplicación funciona en:
- ✅ Desktop (1920px+)
- ✅ Tablet (768px - 1920px)
- ✅ Mobile (< 768px)

En mobile, click en ☰ para abrir menú lateral.

---

## 🐛 Problemas Comunes

### "No puedo iniciar sesión"
1. Verifica que escribiste bien el email
2. Verifica que la contraseña es correcta
3. Asegúrate que el usuario está "Activo"
4. Verifica que las cookies están habilitadas

### "Me dice que no tengo permisos"
1. Verifica tu rol en el perfil
2. Algunos módulos son solo para Admin
3. Los estudiantes solo ven sus documentos propios

### "La contraseña no funciona después de cambiarla"
1. Intenta cerrar sesión e iniciar de nuevo
2. Asegúrate de haber confirmado correctamente

### "No veo los módulos en el menú"
1. Algunos módulos aparecen solo según tu rol
2. Admin ve: Usuarios, Carpetas, Categorías
3. Administrativo ve: Carpetas, Categorías
4. Estudiante solo ve: Sus propios documentos

---

## 🔗 Enlaces Útiles

| Página | URL |
|--------|-----|
| Aplicación | `/Programa-Gestion-SDI/index.html` |
| Login | `/Programa-Gestion-SDI/login.html` |
| Test API | `/Programa-Gestion-SDI/test_api.html` |
| Crear Admin | `/Programa-Gestion-SDI/crear_admin_test.php` |
| Verificación | `/Programa-Gestion-SDI/VERIFICACION_INSTALACION.php` |

---

## 📞 Soporte

### Si algo no funciona:

1. **Abre la consola** del navegador (F12)
   - Busca mensajes de error en rojo

2. **Usa test_api.html** para probar endpoints
   - Verifica que cada endpoint devuelve datos

3. **Revisa logs** (si está configurado)
   - Busca en `public/logs/`

4. **Verifica sesión** en test_api.html
   - Click en "Test de Verificar Sesión"
   - Debe mostrar tus datos

---

## 💡 Tips Útiles

### Cambiar entre usuarios
1. Logout (botón en esquina superior)
2. Login con otro usuario
3. Verás menús diferentes según el rol

### Documentos como Estudiante
- Solo puedes ver y editar los documentos que TÚ creaste
- No puedes ver documentos de otros estudiantes
- Puedes ver documentos de Admin/Administrativo

### Organización recomendada
1. Crea **Carpetas Físicas** por área/departamento
2. Crea **Categorías** por tipo de documento
3. Agrega **Campos Dinámicos** relevantes por categoría
4. Luego **Crea Documentos** seleccionando la categoría correcta

---

## ✅ Checklist de Instalación

- [ ] Base de datos creada y schema.sql importado
- [ ] Usuarios de prueba creados (crear_admin_test.php)
- [ ] Apache con mod_rewrite habilitado
- [ ] Archivo .htaccess en la raíz del proyecto
- [ ] config/db.php con credenciales correctas
- [ ] Carpeta public/uploads/ con permisos de escritura (777)
- [ ] Acceso a index.html sin errores
- [ ] Login funciona con admin@sdi.local
- [ ] test_api.html muestra endpoints funcionales
- [ ] Dashboard carga estadísticas

---

**¡Listo para comenzar!** 🎉

Para más detalles técnicos, lee `DOCUMENTACION_TECNICA.md`
