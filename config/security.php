<?php
define('SECRET_KEY', 'SintekUnity_SecretKey_2026!');
define('SECRET_IV', 'Sintek_IV_2026');
define('ENCRYPT_METHOD', 'AES-256-CBC');

/**
 * Encripta un ID (hasheo reversible para URL y AJAX)
 */
function encrypt_id($id) {
    if (empty($id)) return '';
    $key = hash('sha256', SECRET_KEY);
    $iv = substr(hash('sha256', SECRET_IV), 0, 16);
    $output = openssl_encrypt($id, ENCRYPT_METHOD, $key, 0, $iv);
    return base64_encode($output);
}

/**
 * Desencripta un ID hasheado
 */
function decrypt_id($encrypted_id) {
    if (empty($encrypted_id)) return '';
    $key = hash('sha256', SECRET_KEY);
    $iv = substr(hash('sha256', SECRET_IV), 0, 16);
    $output = openssl_decrypt(base64_decode($encrypted_id), ENCRYPT_METHOD, $key, 0, $iv);
    return $output;
}

/**
 * Valida la sesión del usuario
 */
function require_login() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: /unity2/index.php?page=login");
        exit;
    }
}

/**
 * Genera un token CSRF y lo guarda en la sesión.
 */
function generar_token_csrf() {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica si el token CSRF recibido coincide con el de la sesión.
 */
function verificar_token_csrf($token_recibido) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token_recibido)) {
        return true;
    }
    return false;
}

/**
 * Escapa HTML de manera centralizada para evitar ataques XSS
 */
function escape_html($string) {
    if (is_null($string)) { return ''; }
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}
?>
