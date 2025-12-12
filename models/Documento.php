<?php
/**
 * Modelo Documento - SDI Gestión Documental
 * Maneja todas las operaciones relacionadas con documentos de auditoría
 * 
 * Seguridad: Todas las consultas usan Prepared Statements
 */

require_once __DIR__ . '/../config/autoload.php';

class Documento {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Cuenta el total de documentos
     * 
     * @return int Total de documentos
     */
    public function contar(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM documentos_auditoria";
            $stmt = executeQuery($sql);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
            
        } catch (PDOException $e) {
            error_log("Error al contar documentos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cuenta documentos por estado de respaldo
     * 
     * @param string $estado Estado ('pendiente' o 'respaldado')
     * @return int Total de documentos
     */
    public function contarPorEstado(string $estado): int {
        try {
            if (!in_array($estado, [RESPALDO_PENDIENTE, RESPALDO_RESPALDADO])) {
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM documentos_auditoria WHERE respaldo_estado = :estado";
            $stmt = executeQuery($sql, ['estado' => $estado]);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
            
        } catch (PDOException $e) {
            error_log("Error al contar documentos por estado: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cuenta el total de carpetas
     * 
     * @return int Total de carpetas
     */
    public function contarCarpetas(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM carpetas_fisicas";
            $stmt = executeQuery($sql);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
            
        } catch (PDOException $e) {
            error_log("Error al contar carpetas: " . $e->getMessage());
            return 0;
        }
    }
}

