<?php
/**
 * Helper de Seguridad - SDI Gestión Documental
 * Funciones globales para sanitización y protección contra XSS
 * 
 * Protección: Sanitización de entrada y escape de salida
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

function sanitizeInput($data, $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS) {
    if (is_array($data)) {
        return array_map(function($item) use ($filter) {
            return sanitizeInput($item, $filter);
        }, $data);
    }
    if (is_string($data)) {
        $data = trim($data);
        $data = filter_var($data, $filter, FILTER_FLAG_NO_ENCODE_QUOTES);
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data);
        return $data;
    }
    return $data;
}

function getPost(string $key, $default = null) {
    return isset($_POST[$key]) ? sanitizeInput($_POST[$key]) : $default;
}

function getGet(string $key, $default = null) {
    return isset($_GET[$key]) ? sanitizeInput($_GET[$key]) : $default;
}

function escapeOutput(string $data, bool $doubleEncode = false): string {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8', $doubleEncode);
}

function validateEmail(string $email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

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

function validateUrl(string $url) {
    $url = filter_var(trim($url), FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

function sanitizeSqlIdentifier(string $string): string {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $string);
}

/**
 * Función de logging detallada para debugging
 * Escribe en archivo de log y también en error_log de PHP
 * 
 * @param string $message Mensaje principal
 * @param string $level Nivel: DEBUG, INFO, WARNING, ERROR, CRITICAL
 * @param array $context Contexto adicional (debug info)
 * @return void
 */
function logger(string $message, string $level = 'INFO', array $context = []): void {
    if (!defined('APP_ROOT')) {
        return;
    }

    $logsDir = APP_ROOT . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }

    // Formato de timestamp
    $timestamp = date('Y-m-d H:i:s.') . substr(microtime(true), -3);
    
    // Información de contexto
    $contextStr = '';
    if (!empty($context)) {
        $contextStr = ' | CONTEXT: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    // Información del archivo y línea que llamó la función
    $backtrace = debug_backtrace();
    $caller = $backtrace[1] ?? [];
    $file = basename($caller['file'] ?? '');
    $line = $caller['line'] ?? 0;
    $function = $caller['function'] ?? '';

    // Formato de línea de log
    $logLine = sprintf(
        "[%s] [%s] %s (in %s::%s:%d)%s",
        $timestamp,
        str_pad($level, 8),
        $message,
        $file,
        $function,
        $line,
        $contextStr
    );

    // Escribir en archivo de log del día
    $logFile = $logsDir . '/app-' . date('Y-m-d') . '.log';
    @file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND);

    // También escribir en error_log del sistema (visible en /var/log o Windows Event Viewer)
    error_log($logLine);
}
