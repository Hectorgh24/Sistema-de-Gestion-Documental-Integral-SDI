<?php
namespace App\Middleware;

use App\Models\Rol;

/**
 * Middleware: Autorización
 * 
 * Verifica que el usuario tenga los permisos necesarios.
 * Implementa control de acceso basado en roles (RBAC).
 * 
 * Roles disponibles:
 * - Administrador: Acceso total
 * - Personal Administrativo: Documentos, carpetas, categorías (no usuarios)
 * - Estudiante SS: Solo sus propios documentos
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class Autorizacion
{
    /**
     * Verificar si usuario tiene acceso a una acción
     * 
     * @param string $accion Acción a verificar
     * @return bool True si tiene permiso
     */
    public static function verificarAcceso($accion)
    {
        $rol = Autenticacion::getRol();
        
        if (!$rol) {
            return false;
        }

        $rolesModel = new Rol();
        return $rolesModel->tienePermiso($rol, $accion);
    }

    /**
     * Verificar si usuario tiene un rol específico
     * 
     * @param string|array $rolesRequeridos Rol o array de roles
     * @return bool True si tiene el rol
     */
    public static function tieneRol($rolesRequeridos)
    {
        $rolUsuario = Autenticacion::getRol();
        
        if (is_array($rolesRequeridos)) {
            return in_array($rolUsuario, $rolesRequeridos);
        }
        
        return $rolUsuario === $rolesRequeridos;
    }

    /**
     * Verificar si es Administrador
     * 
     * @return bool True si es admin
     */
    public static function esAdministrador()
    {
        return self::tieneRol(Rol::ADMINISTRADOR);
    }

    /**
     * Verificar si es Personal Administrativo
     * 
     * @return bool True si es administrativo
     */
    public static function esAdministrativo()
    {
        return self::tieneRol(Rol::ADMINISTRATIVO);
    }

    /**
     * Verificar si es Estudiante
     * 
     * @return bool True si es estudiante
     */
    public static function esEstudiante()
    {
        return self::tieneRol(Rol::ESTUDIANTE);
    }

    /**
     * Requerir rol específico
     * 
     * @param string|array $rolesRequeridos Rol o array de roles requeridos
     * @throws \Exception Si no tiene el rol
     */
    public static function requerirRol($rolesRequeridos)
    {
        if (!self::tieneRol($rolesRequeridos)) {
            http_response_code(403);
            response(false, 'No tiene permisos para realizar esta acción', null, 403);
        }
    }

    /**
     * Requerir acción específica
     * 
     * @param string $accion Acción requerida
     * @throws \Exception Si no tiene acceso
     */
    public static function requerirAcceso($accion)
    {
        if (!self::verificarAcceso($accion)) {
            http_response_code(403);
            response(false, 'No tiene permisos para: ' . $accion, null, 403);
        }
    }

    /**
     * Verificar si usuario puede editar recurso
     * Solo el propietario o admin pueden editar
     * 
     * @param int $id_usuario_propietario ID del propietario del recurso
     * @return bool True si puede editar
     */
    public static function puedeEditarRecurso($id_usuario_propietario)
    {
        $id_usuario_actual = Autenticacion::getId();
        
        return self::esAdministrador() || $id_usuario_actual === $id_usuario_propietario;
    }

    /**
     * Verificar si usuario puede eliminar recurso
     * Solo admin puede eliminar
     * 
     * @return bool True si puede eliminar
     */
    public static function puedeEliminarRecurso()
    {
        return self::esAdministrador();
    }

    /**
     * Obtener nivel de acceso numérico para comparaciones
     * Mayor número = más permisos
     * 
     * @param string $rol Nombre del rol
     * @return int Nivel de acceso
     */
    public static function obtenerNivelAcceso($rol = null)
    {
        $rol = $rol ?? Autenticacion::getRol();

        $niveles = [
            Rol::ESTUDIANTE        => 1,
            Rol::ADMINISTRATIVO    => 2,
            Rol::ADMINISTRADOR     => 3
        ];

        return $niveles[$rol] ?? 0;
    }

    /**
     * Verificar si un rol tiene mayor o igual acceso que otro
     * 
     * @param string $rol1 Primer rol a comparar
     * @param string $rol2 Segundo rol a comparar
     * @return bool True si rol1 >= rol2
     */
    public static function tieneMayorAcceso($rol1, $rol2)
    {
        return self::obtenerNivelAcceso($rol1) >= self::obtenerNivelAcceso($rol2);
    }
}
?>
