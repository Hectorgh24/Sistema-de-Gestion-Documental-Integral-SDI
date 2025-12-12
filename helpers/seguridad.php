<?php
/**
 * Helper de Seguridad - SDI Gestión Documental
 * Funciones globales para sanitización y protección contra XSS
 * 
 * Protección: Sanitización de entrada y escape de salida
 */

// Prevenir acceso directo
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

/**
 * Sanitiza datos de entrada para prevenir XSS
 * Limpia y valida datos recibidos de $_POST, $_GET, $_REQUEST
 * 
 * @param mixed $data Datos a sanitizar (string, array, etc.)
 * @param string $filter Tipo de filtro a aplicar (default: FILTER_SANITIZE_STRING)
 * @return mixed Datos sanitizados
 */
function sanitizeInput($data, $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS) {
    if (is_array($data)) {
        return array_map(function($item) use ($filter) {
            return sanitizeInput($item, $filter);
        }, $data);
    }
    
    if (is_string($data)) {
        // Eliminar caracteres nulos y espacios en blanco al inicio/final
        $data = trim($data);
        
        // Aplicar filtro de sanitización
        $data = filter_var($data, $filter, FILTER_FLAG_NO_ENCODE_QUOTES);
        
        // Eliminar caracteres de control (excepto saltos de línea y tabs)
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data);
        
        return $data;
    }
    
    return $data;
}

/**
 * Obtiene y sanitiza un valor de $_POST
 * 
 * @param string $key Clave del array $_POST
 * @param mixed $default Valor por defecto si no existe
 * @return mixed Valor sanitizado o default
 */
function getPost(string $key, $default = null) {
    return isset($_POST[$key]) ? sanitizeInput($_POST[$key]) : $default;
}

/**
 * Obtiene y sanitiza un valor de $_GET
 * 
 * @param string $key Clave del array $_GET
 * @param mixed $default Valor por defecto si no existe
 * @return mixed Valor sanitizado o default
 */
function getGet(string $key, $default = null) {
    return isset($_GET[$key]) ? sanitizeInput($_GET[$key]) : $default;
}

/**
 * Escapa datos para salida HTML (protección XSS en output)
 * DEBE usarse siempre al mostrar datos en HTML
 * 
 * @param string $data Datos a escapar
 * @param bool $doubleEncode Si true, codifica también entidades ya codificadas
 * @return string Datos escapados seguros para HTML
 */
function escapeOutput(string $data, bool $doubleEncode = false): string {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8', $doubleEncode);
}

/**
 * Valida y sanitiza un email
 * 
 * @param string $email Email a validar
 * @return string|false Email válido o false si es inválido
 */
function validateEmail(string $email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Valida y sanitiza un número entero
 * 
 * @param mixed $value Valor a validar
 * @param int|null $min Valor mínimo (opcional)
 * @param int|null $max Valor máximo (opcional)
 * @return int|false Entero válido o false si es inválido
 */
function validateInt($value, ?int $min = null, ?int $max = null) {
    $options = [];
    if ($min !== null) {
        $options['options']['min_range'] = $min;
    }
    if ($max !== null) {
        $options['options']['max_range'] = $max;
    }
    
    return filter_var($value, FILTER_VALIDATE_INT, $options);
}

/**
 * Valida y sanitiza un número decimal
 * 
 * @param mixed $value Valor a validar
 * @param float|null $min Valor mínimo (opcional)
 * @param float|null $max Valor máximo (opcional)
 * @return float|false Decimal válido o false si es inválido
 */
function validateFloat($value, ?float $min = null, ?float $max = null) {
    $options = [];
    if ($min !== null) {
        $options['options']['min_range'] = $min;
    }
    if ($max !== null) {
        $options['options']['max_range'] = $max;
    }
    
    return filter_var($value, FILTER_VALIDATE_FLOAT, $options);
}

/**
 * Valida una URL
 * 
 * @param string $url URL a validar
 * @return string|false URL válida o false si es inválida
 */
function validateUrl(string $url) {
    $url = filter_var(trim($url), FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

/**
 * Sanitiza un string para uso en consultas SQL (complemento a prepared statements)
 * Úsalo solo para nombres de columnas/tablas, NO para valores
 * 
 * @param string $string String a sanitizar
 * @return string String sanitizado
 */
function sanitizeSqlIdentifier(string $string): string {
    // Solo permite letras, números y guiones bajos
    return preg_replace('/[^a-zA-Z0-9_]/', '', $string);
}

/**
 * Valida y sanitiza un archivo subido
 * 
 * @param array $file Array $_FILES['nombre_campo']
 * @param array $allowedExtensions Extensiones permitidas (ej: ['pdf', 'docx'])
 * @param int $maxSize Tamaño máximo en bytes (default: 5MB)
 * @return array|false Array con datos del archivo validado o false si hay error
 */
function validateUploadedFile(array $file, array $allowedExtensions = ['pdf', 'docx'], int $maxSize = 5242880) {
    // Verificar que no hubo errores en la subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Verificar tamaño
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Obtener extensión del archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Verificar extensión permitida
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }
    
    // Verificar tipo MIME (validación adicional)
    $allowedMimes = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
        return false;
    }
    
    // Sanitizar nombre del archivo
    $safeName = sanitizeInput(basename($file['name']));
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $safeName);
    
    return [
        'name' => $safeName,
        'tmp_name' => $file['tmp_name'],
        'size' => $file['size'],
        'type' => $mimeType,
        'extension' => $extension
    ];
}

/**
 * Genera un nombre de archivo único y seguro
 * 
 * @param string $originalName Nombre original del archivo
 * @return string Nombre único generado
 */
function generateUniqueFileName(string $originalName): string {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($originalName, '.' . $extension));
    $timestamp = time();
    $random = bin2hex(random_bytes(4));
    
    return sprintf('%s_%s_%s.%s', $safeName, $timestamp, $random, $extension);
}

/**
 * Inicia sesión de forma segura
 * Configura parámetros de seguridad para la sesión
 */
function startSecureSession(): void {
    // Configurar parámetros de sesión seguros
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '0'); // Cambiar a 1 si usas HTTPS
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerar ID de sesión periódicamente para prevenir Session Fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutos
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Regenera el ID de sesión (usar después de login exitoso)
 */
function regenerateSessionId(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Verifica si el usuario está autenticado
 * 
 * @return bool True si está autenticado, false en caso contrario
 */
function isAuthenticated(): bool {
    startSecureSession();
    return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_rol']);
}

/**
 * Requiere autenticación, redirige al login si no está autenticado
 */
function requireAuth(): void {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Requiere un rol específico
 * 
 * @param string|array $roles Rol o array de roles permitidos
 */
function requireRole($roles): void {
    requireAuth();
    
    if (!isset($_SESSION['usuario_rol'])) {
        header('Location: /login.php');
        exit;
    }
    
    $allowedRoles = is_array($roles) ? $roles : [$roles];
    
    if (!in_array($_SESSION['usuario_rol'], $allowedRoles)) {
        http_response_code(403);
        die('Acceso denegado. No tienes permisos para acceder a este recurso.');
    }
}

