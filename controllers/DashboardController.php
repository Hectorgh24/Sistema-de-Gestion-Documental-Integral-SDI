<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Documento;
use App\Middleware\Autenticacion;

/**
 * Controller: DashboardController
 * 
 * Proporciona datos y estadísticas para el dashboard.
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class DashboardController
{
    protected $usuarioModel;
    protected $documentoModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->documentoModel = new Documento();
    }

    /**
     * Obtener estadísticas generales del dashboard
     * GET /api/dashboard/estadisticas
     */
    public function estadisticas()
    {
        try {
            Autenticacion::requerirAutenticacion();

            $estadisticas = [
                'usuarios' => $this->usuarioModel->obtenerEstadisticas(),
                'documentos' => $this->documentoModel->obtenerEstadisticas()
            ];

            response(true, 'Estadísticas obtenidas', $estadisticas, 200);
        } catch (\Exception $e) {
            logger("Error obteniendo estadísticas: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }

    /**
     * Obtener datos del usuario autenticado para mostrar en dashboard
     * GET /api/dashboard/usuario
     */
    public function usuario()
    {
        try {
            Autenticacion::requerirAutenticacion();

            $usuario = Autenticacion::usuario();

            response(true, 'Datos del usuario', $usuario, 200);
        } catch (\Exception $e) {
            logger("Error obteniendo usuario: " . $e->getMessage(), 'ERROR');
            response(false, $e->getMessage(), null, 400);
        }
    }
}
?>
