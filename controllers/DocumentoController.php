<?php
namespace App\Controllers;

use App\Models\Documento;
use App\Models\Categoria;
use App\Models\Carpeta;
use App\Models\Rol;
use App\Middleware\Autenticacion;
use App\Middleware\Autorizacion;

/**
 * Controller: DocumentoController
 * 
 * Gestiona las operaciones CRUD de documentos.
 * Control de acceso: Administrativo y Estudiantes (sus propios documentos)
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class DocumentoController
{
    protected $documentoModel;
    protected $categoriaModel;
    protected $carpetaModel;

    public function __construct()
    {
        $this->documentoModel = new Documento();
        $this->categoriaModel = new Categoria();
        $this->carpetaModel = new Carpeta();
    }

    /**
     * Listar documentos con paginación
     * GET /api/documentos?page=1&limit=10&estado_gestion=pendiente
     */
    public function listar()
    {
        try {
            Autenticacion::requerirAutenticacion();

            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, (int)($_GET['limit'] ?? 10));
            $offset = ($page - 1) * $limit;

            $filtros = [];

            if (!empty($_GET['estado_gestion'])) {
                $filtros['estado_gestion'] = $_GET['estado_gestion'];
            }

            if (!empty($_GET['estado_respaldo'])) {
                $filtros['estado_respaldo'] = $_GET['estado_respaldo'];
            }

            if (!empty($_GET['id_carpeta'])) {
                $filtros['id_carpeta'] = (int)$_GET['id_carpeta'];
            }

            if (!empty($_GET['id_categoria'])) {
                $filtros['id_categoria'] = (int)$_GET['id_categoria'];
            }

            // Estudiantes solo ven sus propios documentos
            if (Autorizacion::esEstudiante()) {
                $filtros['id_usuario_captura'] = Autenticacion::getId();
            }

            $documentos = $this->documentoModel->listar($filtros, $limit, $offset);
            $total = $this->documentoModel->contarConFiltros($filtros);

            $data = [
                'documentos' => $documentos,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];

            response(true, 'Documentos obtenidos', $data, 200);

        } catch (\Exception $e) {
            logger("Error listando documentos: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Obtener documento por ID
     * GET /api/documentos/:id
     */
    public function obtener($id)
    {
        try {
            Autenticacion::requerirAutenticacion();

            $documento = $this->documentoModel->obtenerPorId((int)$id);

            if (!$documento) {
                response(false, 'Documento no encontrado', null, 404);
            }

            // Estudiantes solo pueden ver sus propios documentos
            if (Autorizacion::esEstudiante() && $documento['id_usuario_captura'] !== Autenticacion::getId()) {
                response(false, 'No tiene permisos', null, 403);
            }

            response(true, 'Documento obtenido', $documento, 200);

        } catch (\Exception $e) {
            logger("Error obteniendo documento: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Crear nuevo documento
     * POST /api/documentos
     */
    public function crear()
    {
        try {
            Autenticacion::requerirAutenticacion();
            Autorizacion::requerirAcceso('crear_documento');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                response(false, 'Método no permitido', null, 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                response(false, 'Datos inválidos', null, 400);
            }

            // Validaciones
            if (empty($input['id_categoria'])) {
                response(false, 'Categoría requerida', null, 400);
            }

            if (empty($input['id_carpeta'])) {
                response(false, 'Carpeta requerida', null, 400);
            }

            if (empty($input['fecha_documento'])) {
                response(false, 'Fecha del documento requerida', null, 400);
            }

            // Verificar que la categoría y carpeta existan
            if (!$this->categoriaModel->obtenerPorId($input['id_categoria'])) {
                response(false, 'Categoría inválida', null, 400);
            }

            if (!$this->carpetaModel->obtenerPorId($input['id_carpeta'])) {
                response(false, 'Carpeta inválida', null, 400);
            }

            // Crear documento
            $id_documento = $this->documentoModel->crear([
                'id_categoria'      => $input['id_categoria'],
                'id_carpeta'        => $input['id_carpeta'],
                'id_usuario_captura' => Autenticacion::getId(),
                'fecha_documento'   => $input['fecha_documento'],
                'valores'           => $input['valores'] ?? []
            ]);

            if (!$id_documento) {
                response(false, 'Error al crear documento', null, 500);
            }

            $documento = $this->documentoModel->obtenerPorId($id_documento);

            logger("Documento creado: ID $id_documento", 'INFO');
            response(true, 'Documento creado', $documento, 201);

        } catch (\Exception $e) {
            logger("Error creando documento: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Actualizar documento
     * PUT /api/documentos/:id
     */
    public function actualizar($id)
    {
        try {
            Autenticacion::requerirAutenticacion();
            Autorizacion::requerirAcceso('editar_documento');

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                response(false, 'Método no permitido', null, 405);
            }

            $documento = $this->documentoModel->obtenerPorId((int)$id);

            if (!$documento) {
                response(false, 'Documento no encontrado', null, 404);
            }

            // Estudiantes solo pueden editar sus propios documentos
            if (Autorizacion::esEstudiante() && $documento['id_usuario_captura'] !== Autenticacion::getId()) {
                response(false, 'No puede editar este documento', null, 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                response(false, 'Datos inválidos', null, 400);
            }

            $this->documentoModel->actualizar((int)$id, $input);

            $documentoActualizado = $this->documentoModel->obtenerPorId((int)$id);

            logger("Documento actualizado: ID $id", 'INFO');
            response(true, 'Documento actualizado', $documentoActualizado, 200);

        } catch (\Exception $e) {
            logger("Error actualizando documento: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Cambiar estado de gestión
     * PATCH /api/documentos/:id/estado_gestion
     */
    public function cambiarEstadoGestion($id)
    {
        try {
            Autenticacion::requerirAutenticacion();

            if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
                response(false, 'Método no permitido', null, 405);
            }

            $documento = $this->documentoModel->obtenerPorId((int)$id);

            if (!$documento) {
                response(false, 'Documento no encontrado', null, 404);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || empty($input['estado'])) {
                response(false, 'Estado requerido', null, 400);
            }

            $this->documentoModel->cambiarEstadoGestion((int)$id, $input['estado']);

            $documentoActualizado = $this->documentoModel->obtenerPorId((int)$id);

            response(true, 'Estado actualizado', $documentoActualizado, 200);

        } catch (\Exception $e) {
            logger("Error cambiando estado: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Eliminar documento
     * DELETE /api/documentos/:id
     */
    public function eliminar($id)
    {
        try {
            Autenticacion::requerirAutenticacion();
            Autorizacion::requerirRol(Rol::ADMINISTRADOR);

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                response(false, 'Método no permitido', null, 405);
            }

            $documento = $this->documentoModel->obtenerPorId((int)$id);

            if (!$documento) {
                response(false, 'Documento no encontrado', null, 404);
            }

            $this->documentoModel->cancelar((int)$id);

            logger("Documento eliminado: ID $id", 'INFO');
            response(true, 'Documento eliminado', null, 200);

        } catch (\Exception $e) {
            logger("Error eliminando documento: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Obtener estadísticas de documentos
     * GET /api/documentos/estadisticas
     */
    public function estadisticas()
    {
        try {
            Autenticacion::requerirAutenticacion();

            $stats = $this->documentoModel->obtenerEstadisticas();

            response(true, 'Estadísticas obtenidas', $stats, 200);

        } catch (\Exception $e) {
            logger("Error obteniendo estadísticas: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }
}
?>
