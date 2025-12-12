# SDI Gestión Documental

Sistema de gestión documental desarrollado para entorno de hosting compartido (InfinityFree).

## Requisitos

- PHP 8.x
- MySQL 5.7+ o MariaDB 10.3+
- Apache con mod_rewrite habilitado

## Instalación

1. **Subir archivos** al servidor InfinityFree

2. **Configurar base de datos**:
   - Ejecutar el script SQL proporcionado en `database/schema.sql`
   - Editar `config/db.php` con las credenciales de InfinityFree:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'SDI_Gestion_Documental');
     define('DB_USER', 'tu_usuario');
     define('DB_PASS', 'tu_password');
     ```

3. **Crear directorio de uploads**:
   ```bash
   mkdir public/uploads
   chmod 755 public/uploads
   ```

4. **Crear usuario administrador**:
   - Ejecutar manualmente en la base de datos o usar el script de creación

## Estructura del Proyecto

```
/
├── config/           # Configuración (DB, constantes)
├── controllers/      # Controladores MVC
├── models/           # Modelos de datos
├── views/            # Vistas HTML/PHP
├── helpers/          # Funciones auxiliares
├── public/           # Archivos públicos
│   ├── css/          # Estilos
│   ├── js/           # JavaScript
│   └── uploads/      # Archivos subidos
└── index.php         # Punto de entrada
```

## Seguridad

- ✅ Protección contra SQL Injection (Prepared Statements)
- ✅ Protección contra XSS (Sanitización entrada/salida)
- ✅ Gestión segura de sesiones
- ✅ Validación de archivos subidos
- ✅ Headers de seguridad HTTP

## Despliegue

Para una guía detallada de despliegue, consulta: **[GUIA_DESPLIEGUE.md](GUIA_DESPLIEGUE.md)**

### Verificación Rápida

Después del despliegue, ejecuta el script de verificación:
```
https://tu-dominio.com/VERIFICACION_INSTALACION.php?token=verificar123
```

**IMPORTANTE**: Elimina este archivo después de verificar.

## Licencia

Propietario - Todos los derechos reservados

