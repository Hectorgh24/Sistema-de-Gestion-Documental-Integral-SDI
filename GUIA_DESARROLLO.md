# 📝 Guía de Desarrollo - Extensiones Futuras

Este documento proporciona un patrón claro para agregar nuevos módulos al sistema SDI.

## 📋 Estructura de un Módulo Nuevo

Cuando agregues un nuevo módulo (ejemplo: "Reportes"), sigue esta estructura:

```
Programa-Gestion-SDI/
├── models/
│   └── Reporte.php              ← Modelo de datos
├── controllers/
│   └── ReporteController.php    ← Lógica de negocio
├── public/
│   └── js/
│       ├── modules/
│       │   └── reportes.js      ← Lógica de módulo
│       └── app.js               ← Referencia en cargarModulo()
├── views/
│   └── reportes/
│       └── index.html           ← Interfaz (si se quiere separada)
└── router.php                   ← Detecta automáticamente
```

---

## 🔨 Paso a Paso: Crear un Nuevo Módulo

### Paso 1: Crear el Modelo

**Archivo:** `models/Reporte.php`

```php
<?php
namespace App\Models;

use PDO;

class Reporte {
    private $pdo;
    private $tabla = 'reportes'; // Si la tabla existe en BD

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los reportes
     * @return array Reportes con paginación
     */
    public function listar($limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->tabla}
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener reporte por ID
     */
    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tabla} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo reporte
     */
    public function crear($datos) {
        $this->validar($datos);
        
        // Construir query dinámico
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($campos), '?');
        $sql = "INSERT INTO {$this->tabla} (" . implode(',', $campos) . ") 
                VALUES (" . implode(',', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($datos));
        
        return $this->pdo->lastInsertId();
    }

    /**
     * Actualizar reporte
     */
    public function actualizar($id, $datos) {
        $this->validar($datos);
        
        $campos = array_keys($datos);
        $set = implode('=?, ', $campos) . '=?';
        $sql = "UPDATE {$this->tabla} SET {$set} WHERE id=?";
        
        $stmt = $this->pdo->prepare($sql);
        $valores = array_values($datos);
        $valores[] = $id;
        
        return $stmt->execute($valores);
    }

    /**
     * Eliminar reporte
     */
    public function eliminar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tabla} WHERE id=?");
        return $stmt->execute([$id]);
    }

    /**
     * Validar datos del reporte
     */
    private function validar($datos) {
        // Implementar validaciones según necesites
        if (isset($datos['nombre']) && empty($datos['nombre'])) {
            throw new Exception('El nombre es requerido');
        }
        // Más validaciones...
    }
}
```

### Paso 2: Crear el Controlador

**Archivo:** `controllers/ReporteController.php`

```php
<?php
namespace App\Controllers;

use App\Models\Reporte;
use App\Middleware\Autenticacion;
use App\Middleware\Autorizacion;

class ReporteController {
    private $modelo;
    private $auth;
    private $autorizacion;

    public function __construct($pdo) {
        $this->modelo = new Reporte($pdo);
        $this->auth = new Autenticacion();
        $this->autorizacion = new Autorizacion();
    }

    /**
     * Listar reportes
     * GET /api/reportes
     */
    public function listar() {
        // Requerir autenticación
        if (!$this->auth->verificar()) {
            http_response_code(401);
            return ['success' => false, 'message' => 'No autenticado'];
        }

        // Validar permisos (si es necesario)
        // $this->autorizacion->requerirRol(['Administrador', 'Personal Administrativo']);

        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

        try {
            $datos = $this->modelo->listar($limit, $offset);
            return [
                'success' => true,
                'data' => ['reportes' => $datos],
                'limit' => $limit,
                'offset' => $offset
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtener reporte individual
     * GET /api/reportes/:id
     */
    public function obtener($id) {
        if (!$this->auth->verificar()) {
            http_response_code(401);
            return ['success' => false, 'message' => 'No autenticado'];
        }

        try {
            $dato = $this->modelo->obtenerPorId($id);
            if (!$dato) {
                http_response_code(404);
                return ['success' => false, 'message' => 'Reporte no encontrado'];
            }

            return ['success' => true, 'data' => $dato];
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Crear reporte
     * POST /api/reportes
     */
    public function crear() {
        if (!$this->auth->verificar()) {
            http_response_code(401);
            return ['success' => false, 'message' => 'No autenticado'];
        }

        // Requerir rol específico si es necesario
        if (!$this->autorizacion->tieneRol(['Administrador'])) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Acceso denegado'];
        }

        try {
            // Obtener datos del body
            $input = json_decode(file_get_contents('php://input'), true);

            // Validar entrada
            if (empty($input['nombre'])) {
                http_response_code(400);
                return ['success' => false, 'message' => 'El nombre es requerido'];
            }

            $id = $this->modelo->crear($input);

            http_response_code(201);
            return [
                'success' => true,
                'message' => 'Reporte creado exitosamente',
                'data' => ['id' => $id]
            ];
        } catch (Exception $e) {
            http_response_code(400);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Actualizar reporte
     * PUT /api/reportes/:id
     */
    public function actualizar($id) {
        if (!$this->auth->verificar()) {
            http_response_code(401);
            return ['success' => false, 'message' => 'No autenticado'];
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if ($this->modelo->actualizar($id, $input)) {
                return [
                    'success' => true,
                    'message' => 'Reporte actualizado exitosamente'
                ];
            } else {
                http_response_code(400);
                return ['success' => false, 'message' => 'No se pudo actualizar'];
            }
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Eliminar reporte
     * DELETE /api/reportes/:id
     */
    public function eliminar($id) {
        if (!$this->auth->verificar()) {
            http_response_code(401);
            return ['success' => false, 'message' => 'No autenticado'];
        }

        if (!$this->autorizacion->tieneRol(['Administrador'])) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Acceso denegado'];
        }

        try {
            if ($this->modelo->eliminar($id)) {
                return ['success' => true, 'message' => 'Reporte eliminado'];
            } else {
                return ['success' => false, 'message' => 'No se encontró el reporte'];
            }
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
```

### Paso 3: Crear Función en app.js

En `public/js/app.js`, agregar en la función `cargarModulo()`:

```javascript
case 'reportes':
    html = await cargarReportes();
    break;
```

Y agregar la función:

```javascript
/**
 * Cargar módulo de reportes
 */
async function cargarReportes() {
    const resultado = await api.get('/api/reportes', { limit: 20 });

    if (!resultado.success) {
        return '<p class="text-red-500">Error cargando reportes</p>';
    }

    const reportes = resultado.data.reportes || [];

    let html = `
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Reportes</h1>
                <button onclick="mostrarFormularioNuevoReporte()" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Nuevo Reporte
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Fecha</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
    `;

    reportes.forEach(reporte => {
        html += `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">${reporte.nombre}</td>
                <td class="px-4 py-2">${new Date(reporte.fecha).toLocaleDateString('es-ES')}</td>
                <td class="px-4 py-2">
                    <button onclick="verReporte(${reporte.id})" class="text-blue-500 hover:text-blue-700 mr-2">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="eliminarReporte(${reporte.id})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

    return html;
}
```

### Paso 4: Agregar al Menú

En `index.html`, agregar en el nav:

```html
<a href="#" onclick="cargarModulo('reportes')" class="nav-link flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 cursor-pointer" data-modulo="reportes">
    <i class="fas fa-chart-bar w-5"></i>
    <span class="ml-3">Reportes</span>
</a>
```

### Paso 5: Router lo Detecta Automáticamente

¡El router.php detectará automáticamente tu nuevo controlador!

No necesitas modificar router.php. Automáticamente mapeará:
- `/api/reportes` → ReporteController
- GET → listar()
- POST → crear()
- GET /:id → obtener()
- PUT /:id → actualizar()
- DELETE /:id → eliminar()

---

## 🔒 Patrones de Seguridad

### Patrón 1: Verificar Autenticación
```php
if (!$this->auth->verificar()) {
    http_response_code(401);
    return ['success' => false, 'message' => 'No autenticado'];
}
```

### Patrón 2: Verificar Rol Específico
```php
if (!$this->autorizacion->tieneRol(['Administrador'])) {
    http_response_code(403);
    return ['success' => false, 'message' => 'Acceso denegado'];
}
```

### Patrón 3: Validar Entrada
```php
if (empty($input['nombre'])) {
    http_response_code(400);
    return ['success' => false, 'message' => 'El nombre es requerido'];
}
```

### Patrón 4: Try/Catch para Errores
```php
try {
    // Lógica
} catch (Exception $e) {
    http_response_code(500);
    return ['success' => false, 'message' => $e->getMessage()];
}
```

---

## 📋 Checklist para Nuevo Módulo

- [ ] Crear tabla en BD (si es necesario)
- [ ] Crear `models/MiModelo.php` con CRUD
- [ ] Crear `controllers/MiModuloController.php`
- [ ] Agregar función `cargarMiModulo()` en `public/js/app.js`
- [ ] Agregar menú en `index.html`
- [ ] Agregar línea case en `cargarModulo()`
- [ ] Agregar permisos si es necesario
- [ ] Probar con `test_api.html`
- [ ] Documentar en DOCUMENTACION_TECNICA.md

---

## 🧪 Testing de Nuevo Módulo

1. Abrir `test_api.html`
2. Agregar botón de test para tu módulo
3. Probar GET /api/mimodulo
4. Probar POST /api/mimodulo con datos
5. Verificar en log que se ejecutó correctamente

---

## 💡 Mejores Prácticas

1. **Usar transacciones para operaciones múltiples**
   ```php
   $this->pdo->beginTransaction();
   try {
       // Múltiples operaciones
       $this->pdo->commit();
   } catch (Exception $e) {
       $this->pdo->rollBack();
   }
   ```

2. **Validar antes de insertar/actualizar**
   ```php
   private function validar($datos) {
       if (empty($datos['campo'])) {
           throw new Exception('Campo requerido');
       }
   }
   ```

3. **Usar constantes para http_response_code**
   ```php
   // Bien
   http_response_code(201); // Created
   http_response_code(400); // Bad Request
   http_response_code(401); // Unauthorized
   http_response_code(403); // Forbidden
   http_response_code(404); // Not Found
   http_response_code(500); // Server Error
   ```

4. **Loguear acciones importantes** (si está implementado)
   ```php
   logger('usuario_' . $this->auth->getId() . '_creo_reporte');
   ```

5. **Usar consistent response format**
   ```php
   // Siempre devolver
   [
       'success' => true/false,
       'message' => 'Descripción del resultado',
       'data' => [...] // Si aplica
   ]
   ```

---

## 📚 Ejemplos Existentes

Para referencia, consulta estos módulos existentes:

1. **Usuario:** `controllers/UsuarioController.php` - Ejemplo completo
2. **Documento:** `controllers/DocumentoController.php` - Con estados y filtros
3. **Carpeta:** `controllers/CarpetaController.php` - Simple CRUD

---

**Versión:** 2.0.0  
**Última actualización:** Enero 2024
