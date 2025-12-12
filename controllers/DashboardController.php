<?php
/**
 * Controlador del Dashboard - SDI Gestión Documental
 * Maneja la visualización del dashboard principal según el rol del usuario
 * 
 * Seguridad: Requiere autenticación
 */

require_once __DIR__ . '/../config/autoload.php';

class DashboardController {
    
    /**
     * Muestra el dashboard principal
     */
    public function mostrar() {
        // Requerir autenticación
        requireAuth();
        
        // Obtener datos del usuario de la sesión
        $usuario = [
            'id' => $_SESSION['usuario_id'] ?? 0,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'rol' => $_SESSION['usuario_rol'] ?? '',
            'id_rol' => $_SESSION['usuario_id_rol'] ?? 0
        ];
        
        // Obtener estadísticas según el rol
        $estadisticas = $this->obtenerEstadisticas($usuario['rol']);
        
        // Obtener módulos disponibles según el rol
        $modulos = $this->obtenerModulos($usuario['rol']);
        
        // Cargar la vista
        require_once __DIR__ . '/../views/dashboard.php';
    }
    
    /**
     * Obtiene las estadísticas del dashboard según el rol
     * 
     * @param string $rol Rol del usuario
     * @return array Estadísticas
     */
    private function obtenerEstadisticas(string $rol): array {
        $estadisticas = [];
        
        try {
            require_once __DIR__ . '/../models/Documento.php';
            $documentoModel = new Documento();
            
            switch ($rol) {
                case ROL_ADMINISTRADOR:
                    // Estadísticas completas para administrador
                    $estadisticas = [
                        'total_documentos' => $documentoModel->contar(),
                        'documentos_pendientes' => $documentoModel->contarPorEstado('pendiente'),
                        'documentos_respaldados' => $documentoModel->contarPorEstado('respaldado'),
                        'total_carpetas' => $documentoModel->contarCarpetas(),
                        'total_usuarios' => $this->contarUsuarios()
                    ];
                    break;
                    
                case ROL_ACADEMICO:
                    // Estadísticas para académico
                    $estadisticas = [
                        'total_documentos' => $documentoModel->contar(),
                        'documentos_pendientes' => $documentoModel->contarPorEstado('pendiente'),
                        'documentos_respaldados' => $documentoModel->contarPorEstado('respaldado'),
                        'total_carpetas' => $documentoModel->contarCarpetas()
                    ];
                    break;
                    
                case ROL_ALUMNO:
                    // Estadísticas limitadas para alumno
                    $estadisticas = [
                        'documentos_disponibles' => $documentoModel->contar(),
                        'carpetas_disponibles' => $documentoModel->contarCarpetas()
                    ];
                    break;
                    
                default:
                    $estadisticas = [];
            }
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            $estadisticas = [];
        }
        
        return $estadisticas;
    }
    
    /**
     * Obtiene los módulos disponibles según el rol
     * 
     * @param string $rol Rol del usuario
     * @return array Módulos disponibles
     */
    private function obtenerModulos(string $rol): array {
        $modulos = [];
        
        switch ($rol) {
            case ROL_ADMINISTRADOR:
                $modulos = [
                    [
                        'titulo' => 'Gestión de Documentos',
                        'descripcion' => 'Administrar documentos de auditoría',
                        'icono' => 'document-text',
                        'url' => '/documentos.php',
                        'color' => 'blue'
                    ],
                    [
                        'titulo' => 'Carpetas Físicas',
                        'descripcion' => 'Gestionar carpetas físicas',
                        'icono' => 'folder',
                        'url' => '/carpetas.php',
                        'color' => 'green'
                    ],
                    [
                        'titulo' => 'Usuarios',
                        'descripcion' => 'Administrar usuarios del sistema',
                        'icono' => 'users',
                        'url' => '/usuarios.php',
                        'color' => 'purple'
                    ],
                    [
                        'titulo' => 'Reportes',
                        'descripcion' => 'Ver reportes y estadísticas',
                        'icono' => 'chart-bar',
                        'url' => '/reportes.php',
                        'color' => 'orange'
                    ]
                ];
                break;
                
            case ROL_ACADEMICO:
                $modulos = [
                    [
                        'titulo' => 'Gestión de Documentos',
                        'descripcion' => 'Administrar documentos de auditoría',
                        'icono' => 'document-text',
                        'url' => '/documentos.php',
                        'color' => 'blue'
                    ],
                    [
                        'titulo' => 'Carpetas Físicas',
                        'descripcion' => 'Ver y gestionar carpetas',
                        'icono' => 'folder',
                        'url' => '/carpetas.php',
                        'color' => 'green'
                    ],
                    [
                        'titulo' => 'Mis Documentos',
                        'descripcion' => 'Documentos capturados por mí',
                        'icono' => 'document-duplicate',
                        'url' => '/mis-documentos.php',
                        'color' => 'indigo'
                    ]
                ];
                break;
                
            case ROL_ALUMNO:
                $modulos = [
                    [
                        'titulo' => 'Consultar Documentos',
                        'descripcion' => 'Buscar y consultar documentos',
                        'icono' => 'search',
                        'url' => '/consultar.php',
                        'color' => 'blue'
                    ],
                    [
                        'titulo' => 'Carpetas Disponibles',
                        'descripcion' => 'Ver carpetas disponibles',
                        'icono' => 'folder-open',
                        'url' => '/carpetas.php',
                        'color' => 'green'
                    ]
                ];
                break;
                
            default:
                $modulos = [];
        }
        
        return $modulos;
    }
    
    /**
     * Cuenta el total de usuarios (solo para administrador)
     * 
     * @return int Total de usuarios
     */
    private function contarUsuarios(): int {
        try {
            require_once __DIR__ . '/../models/Usuario.php';
            $usuarioModel = new Usuario();
            return $usuarioModel->contar();
        } catch (Exception $e) {
            error_log("Error al contar usuarios: " . $e->getMessage());
            return 0;
        }
    }
}

