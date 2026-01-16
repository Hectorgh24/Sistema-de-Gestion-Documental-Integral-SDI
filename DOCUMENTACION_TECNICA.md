# SDI - Gestión Documental

Sistema de Gestión Documental (SDI) desarrollado con arquitectura MVC + AJAX, proporcionando una interfaz moderna para la gestión de documentos, carpetas, categorías y usuarios.

## 📋 Descripción General

SDI es una aplicación web completa para la gestión centralizada de documentos organizacionales, con soporte para:

- **Gestión de Usuarios**: Control administrativo de cuentas (solo Administrador)
- **Gestión de Documentos**: Creación, edición y seguimiento de documentos con estados
- **Gestión de Carpetas Físicas**: Organización de documentos en carpetas físicas
- **Gestión de Categorías**: Definición de tipos de documentos con campos dinámicos
- **Control de Acceso**: Sistema RBAC (Role-Based Access Control) con 3 niveles
- **Dashboard**: Estadísticas y resumen de la actividad del sistema

## 🏗️ Arquitectura

### Estructura de Capas

```
┌─────────────────────────────────────────────────┐
│         Frontend (HTML + JavaScript)            │
│  index.html, login.html + public/js/modules     │
└──────────────────┬──────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────┐
│      REST API (router.php)                      │
│  - Enrutador centralizado                       │
│  - Auto-detección de método HTTP                │
│  - Manejo de errores y respuestas JSON          │
└──────────────────┬──────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────┐
│    Controladores (controllers/)                 │
│  - Lógica de negocio                            │
│  - Validaciones                                 │
│  - Respuestas estructuradas                     │
└──────────────────┬──────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────┐
│    Middleware (middleware/)                     │
│  - Autenticación (sesiones)                     │
│  - Autorización (RBAC)                          │
│  - Control de permisos                          │
└──────────────────┬──────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────┐
│     Modelos (models/)                           │
│  - Acceso a datos (PDO)                         │
│  - Validaciones de negocio                      │
│  - Queries preparadas (SQL seguro)              │
└──────────────────┬──────────────────────────────┘
                   │
└──────────────────▼──────────────────────────────┐
                 Base de Datos (8 tablas)
└─────────────────────────────────────────────────┘
```

### Espacios de Nombres (PSR-4)

```
App\
  ├── Models\
  │   ├── Usuario.php
  │   ├── Rol.php
  │   ├── Documento.php
  │   ├── Carpeta.php
  │   └── Categoria.php
  ├── Controllers\
  │   ├── AuthController.php
  │   ├── UsuarioController.php
  │   ├── DocumentoController.php
  │   ├── CarpetaController.php
  │   ├── CategoriaController.php
  │   ├── DashboardController.php
  │   └── ErrorController.php
  └── Middleware\
      ├── Autenticacion.php
      └── Autorizacion.php
```

## 🔐 Sistema de Roles y Permisos

### Roles Disponibles

| Rol | Nivel | Usuarios | Documentos | Carpetas | Categorías |
|-----|-------|----------|-----------|----------|-----------|
| **Administrador** | 3 | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ CRUD |
| **Personal Administrativo** | 2 | ❌ | ✅ CRUD | ✅ CRUD | ✅ CRUD |
| **Estudiante SS** | 1 | ❌ | ✅ (propios) | ❌ | ❌ |

### Matriz de Permisos

```php
// Por rol
Administrador:
  - crear_usuario, editar_usuario, eliminar_usuario
  - crear_documento, editar_documento, eliminar_documento
  - crear_carpeta, editar_carpeta, eliminar_carpeta
  - crear_categoria, editar_categoria, eliminar_categoria

Personal Administrativo:
  - crear_documento, editar_documento
  - crear_carpeta, editar_carpeta, eliminar_carpeta
  - crear_categoria, editar_categoria

Estudiante SS:
  - crear_documento (solo propio)
  - editar_documento (solo propio)
```

## 🚀 Instalación y Configuración

### Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/XAMPP con mod_rewrite habilitado
- Acceso a la raíz del proyecto

### Pasos de Instalación

1. **Clonar/Descargar el proyecto**
   ```bash
   git clone <repositorio> Programa-Gestion-SDI
   cd Programa-Gestion-SDI
   ```

2. **Configurar la base de datos**
   ```bash
   # En MySQL/phpMyAdmin
   mysql> CREATE DATABASE sdi_gestion;
   mysql> USE sdi_gestion;
   mysql> SOURCE database/schema.sql;
   mysql> SOURCE database/crear_admin.php;  # Opcional: crear usuario admin
   ```

3. **Configurar conexión a BD**
   
   Editar `config/db.php`:
   ```php
   'host' => 'localhost',
   'dbname' => 'sdi_gestion',
   'user' => 'root',
   'pass' => 'password'
   ```

4. **Verificar instalación**
   
   Abrir en navegador:
   ```
   http://localhost/Programa-Gestion-SDI/test_api.html
   ```

5. **Acceder a la aplicación**
   
   ```
   http://localhost/Programa-Gestion-SDI/index.html
   ```

   Credenciales de prueba:
   - Email: `admin@sdi.local`
   - Contraseña: `admin123`

## 📁 Estructura de Archivos

```
Programa-Gestion-SDI/
├── models/                       # Modelos PDO
├── controllers/                  # Controladores
├── middleware/                   # Middleware (Auth, Autorizacion)
├── services/                     # Servicios reutilizables
├── config/                       # Configuración
│   ├── autoload.php              # PSR-4 autoloader
│   ├── constants.php             # Constantes de aplicación
│   └── db.php                    # Configuración de BD
├── database/                     # Scripts de BD
│   ├── schema.sql                # Estructura de tablas
│   └── crear_admin.php           # Crear usuario admin
├── public/                       # Archivos públicos
│   ├── js/                       # JavaScript
│   │   ├── api.js                # Cliente API
│   │   ├── auth.js               # Gestión de autenticación
│   │   ├── ui.js                 # Componentes UI
│   │   └── app.js                # Inicialización principal
│   ├── css/                      # Estilos (Tailwind CDN)
│   └── uploads/                  # Almacenamiento de archivos
├── views/                        # Vistas HTML
│   ├── layouts/                  # Plantillas base
│   └── modules/                  # Vistas de módulos
├── index.html                    # Entrada principal
├── login.html                    # Página de login
├── router.php                    # Enrutador REST API
├── test_api.html                 # Prueba de endpoints
└── README.md                     # Este archivo
```

## 🔌 API REST Endpoints

### Autenticación

```
POST   /api/auth/login              # Iniciar sesión
GET    /api/auth/verificar          # Verificar sesión actual
POST   /api/auth/logout             # Cerrar sesión
POST   /api/auth/cambiarPassword    # Cambiar contraseña
```

### Usuarios (Admin Only)

```
GET    /api/usuarios                # Listar usuarios (paginado)
POST   /api/usuarios                # Crear usuario
GET    /api/usuarios/:id            # Obtener detalles usuario
PUT    /api/usuarios/:id            # Actualizar usuario
DELETE /api/usuarios/:id            # Eliminar usuario
PATCH  /api/usuarios/:id/estado     # Cambiar estado usuario
GET    /api/usuarios/roles          # Listar roles disponibles
GET    /api/usuarios/estadisticas   # Estadísticas de usuarios
```

### Documentos

```
GET    /api/documentos              # Listar documentos (filtrable)
POST   /api/documentos              # Crear documento
GET    /api/documentos/:id          # Obtener detalles documento
PUT    /api/documentos/:id          # Actualizar documento
DELETE /api/documentos/:id          # Eliminar documento (soft delete)
PATCH  /api/documentos/:id/estado   # Cambiar estado gestión
PATCH  /api/documentos/:id/respaldo # Cambiar estado respaldo digital
GET    /api/documentos/estadisticas # Estadísticas de documentos
```

### Carpetas Físicas

```
GET    /api/carpetas                # Listar carpetas
POST   /api/carpetas                # Crear carpeta
GET    /api/carpetas/:id            # Obtener detalles carpeta
PUT    /api/carpetas/:id            # Actualizar carpeta
DELETE /api/carpetas/:id            # Eliminar carpeta
```

### Categorías

```
GET    /api/categorias              # Listar categorías
POST   /api/categorias              # Crear categoría
GET    /api/categorias/:id          # Obtener detalles categoría
PUT    /api/categorias/:id          # Actualizar categoría
DELETE /api/categorias/:id          # Eliminar categoría (soft delete)
```

### Dashboard

```
GET    /api/dashboard/estadisticas  # Estadísticas generales
GET    /api/dashboard/usuario       # Datos del usuario autenticado
```

## 🔐 Seguridad

### Medidas Implementadas

1. **SQL Injection Prevention**
   - Prepared Statements con PDO
   - Validación de entrada en todos los endpoints

2. **Password Security**
   - Hashing con BCRYPT (cost 10)
   - Validación de fortaleza de contraseña
   - Cambio de contraseña seguro

3. **Session Management**
   - Cookies HTTP-only
   - SameSite cookie policy
   - Regeneración de session ID al login

4. **Access Control**
   - RBAC (Role-Based Access Control)
   - Verificación de autenticación en cada request
   - Validación de permisos por endpoint

5. **CORS Protection**
   - Headers restrictivos configurados
   - Origin validation

## 📊 Modelo de Datos

### Tablas Principales

- `roles`: Definición de roles del sistema
- `usuarios`: Registro de usuarios con roles
- `carpetas_fisicas`: Carpetas para organizar documentos
- `cat_categorias`: Tipos de documentos
- `conf_columnas_categoria`: Campos dinámicos por categoría
- `registros_documentos`: Documentos con estados
- `detalles_valores_documento`: Valores dinámicos (patrón EAV)
- `archivos_adjuntos`: Archivos asociados a documentos

### Estados de Documentos

**estado_gestion** (flujo de trabajo):
- `pendiente`: Creado, sin procesar
- `en_revision`: En evaluación
- `archivado`: Completado
- `cancelado`: Descartado

**estado_respaldo_digital** (backup):
- `sin_respaldo`: No tiene copia digital
- `con_respaldo`: Tiene copia digital

## 🛠️ Desarrollo

### Convenciones de Código

1. **PHP**
   - PSR-4 autoloading con namespaces
   - PascalCase para clases
   - camelCase para métodos y propiedades
   - UPPERCASE para constantes

2. **JavaScript**
   - camelCase para funciones y variables
   - Async/await para operaciones asincrónicas
   - Comentarios documenting JSDoc style

3. **CSS**
   - Tailwind CSS utility-first
   - Mobile-first responsive design
   - BEM naming para componentes custom

### Extensión de Funcionalidad

Para agregar un nuevo módulo:

1. Crear modelo en `models/MiModelo.php`
2. Crear controlador en `controllers/MiModuloController.php`
3. Agregar métodos CRUD con validaciones
4. Verificar permisos en controlador con middleware
5. Crear vista HTML y JavaScript en `public/js/modules/`
6. Router detectará automáticamente los endpoints

## 📈 Estadísticas y Reportes

El dashboard proporciona:

- Total de usuarios activos/inactivos
- Total de documentos por estado
- Documentos pendientes de revisión
- Documentos con/sin respaldo digital
- Últimas actividades del sistema

## 🐛 Troubleshooting

### Error: "No se puede conectar a la base de datos"
```
Solución: Verificar credenciales en config/db.php
          Asegurarse que MySQL está corriendo
          Verificar que la base de datos existe
```

### Error: "404 - Endpoint no encontrado"
```
Solución: Verificar que router.php está en la raíz
          Revisar que el controlador existe en controllers/
          Verificar la URL del endpoint (case-sensitive)
```

### Error: "403 - Acceso denegado"
```
Solución: Verificar que el usuario tiene el rol requerido
          Revisar middleware/Autorizacion.php para permisos
          Asegurarse que la sesión está activa
```

### Error: "401 - No autenticado"
```
Solución: Iniciar sesión en login.html
          Verificar que las cookies están habilitadas
          Revisar que auth.verificar() devuelve true
```

## 📞 Soporte

Para problemas, preguntas o sugerencias:

1. Revisar logs en `public/logs/` (si están habilitados)
2. Usar `test_api.html` para debugguear endpoints
3. Revisar console.log() en navegador (DevTools)
4. Verificar errores en error.log de Apache/PHP

## 📄 Licencia

Sistema propietario de gestión documental. Todos los derechos reservados.

---

**Última actualización:** Enero 2024  
**Versión:** 2.0.0  
**Ambiente:** Production-Ready
