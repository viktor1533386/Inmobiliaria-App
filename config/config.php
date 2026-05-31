<?php
// ============================================================
//  CONFIGURACIÓN GLOBAL – Bienes Raíces Framework MVC
// ============================================================

// --- BASE URL ---
// Cambia 'APF1-INTEGRADOR' si renombras la carpeta en htdocs
$is_localhost = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
if ($is_localhost) {
    $default_url = 'http://localhost/APF1-INTEGRADOR/public';
} else {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $default_url = $protocol . '://' . $host;
}

$env_base_url = getenv('BASE_URL');
if ($env_base_url) {
    $env_base_url = rtrim($env_base_url, '/');
    if (substr($env_base_url, -7) === '/public') {
        $env_base_url = substr($env_base_url, 0, -7);
    }
    define('BASE_URL', $env_base_url);
} else {
    define('BASE_URL', $default_url);
}

// --- RUTAS ABSOLUTAS ---
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/public/uploads/propiedades/');
define('UPLOAD_URL', BASE_URL . '/uploads/propiedades/');
define('LOG_FILE',   APP_ROOT . '/logs/auth.log');

// --- BASE DE DATOS ---
define('DB_HOST',    getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost');
define('DB_USER',    getenv('DB_USER') !== false ? getenv('DB_USER') : 'root');
define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME',    getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'bienes_raices');
define('DB_CHARSET', 'utf8mb4');

// --- APP ---
define('APP_NAME',    'Hogar Ideal Perú');
define('APP_TAGLINE', 'Tu hogar perfecto, garantizado');
