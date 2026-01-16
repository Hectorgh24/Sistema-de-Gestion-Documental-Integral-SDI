<?php
namespace App\Controllers;

/**
 * Controller: ErrorController
 * 
 * Maneja errores y rutas no encontradas.
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class ErrorController
{
    /**
     * Manejar 404 - No encontrado
     */
    public function notFound()
    {
        http_response_code(404);
        response(false, 'Recurso no encontrado', null, 404);
    }

    /**
     * Manejar 403 - Prohibido
     */
    public function forbidden()
    {
        http_response_code(403);
        response(false, 'Acceso prohibido', null, 403);
    }

    /**
     * Manejar 500 - Error interno
     */
    public function error()
    {
        http_response_code(500);
        response(false, 'Error interno del servidor', null, 500);
    }
}
?>
