<?php
namespace App\Models;

use PDO;

/**
 * Model: Rol
 * 
 * Gestiona los roles del sistema.
 * Los roles determinan qué acciones puede realizar cada usuario.
 * 
 * Roles del sistema:
 * - Administrador: Control total del sistema
 * - Personal Administrativo: Gestión de documentos y carpetas, NO usuarios
 * - Estudiante SS: Crear y ver sus propios documentos
 * 
 * @author SDI Development Team
 * @version 2.0
 */
class Rol
{
    protected $db;
    protected const TABLE = 'roles';

    // Constantes para roles
    public const ADMINISTRADOR = 'Administrador';
    public const ADMINISTRATIVO = 'Personal Administrativo';
    public const ESTUDIANTE = 'Estudiante SS';

    /**
     * Constructor
     * 
     * @param PDO $db Conexión a base de datos
     */
    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? getDBConnection();
    }

    /**
     * Obtener rol por ID
     * 
     * @param int $id_rol ID del rol
     * @return array|null Datos del rol
     */
    public function obtenerPorId($id_rol)
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id_rol = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_rol]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtener rol por nombre
     * 
     * @param string $nombre Nombre del rol
     * @return array|null Datos del rol
     */
    public function obtenerPorNombre($nombre)
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE nombre_rol = :nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Listar todos los roles
     * 
     * @return array Arreglo de todos los roles
     */
    public function listar()
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY nombre_rol ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si un rol tiene cierto permisos
     * Esto es simplificado; un sistema real usaría una tabla de permisos-roles
     * 
     * @param string $nombreRol Nombre del rol
     * @param string $accion Acción a verificar
     * @return bool True si el rol puede realizar la acción
     */
    public function tienePermiso($nombreRol, $accion)
    {
        $permisos = [
            self::ADMINISTRADOR => [
                'crear_usuario', 'editar_usuario', 'eliminar_usuario',
                'crear_documento', 'editar_documento', 'eliminar_documento',
                'crear_carpeta', 'editar_carpeta', 'eliminar_carpeta',
                'crear_categoria', 'editar_categoria', 'eliminar_categoria',
                'ver_reportes', 'ver_configuracion'
            ],
            self::ADMINISTRATIVO => [
                'crear_documento', 'editar_documento', 'eliminar_documento',
                'crear_carpeta', 'editar_carpeta', 'eliminar_carpeta',
                'crear_categoria', 'editar_categoria', // NO eliminar categoría
                'ver_reportes'
            ],
            self::ESTUDIANTE => [
                'crear_documento', 'editar_documento', // Solo los propios
                'crear_carpeta', 'editar_carpeta'      // Solo las propias
            ]
        ];

        return in_array($accion, $permisos[$nombreRol] ?? []);
    }
}
?>
