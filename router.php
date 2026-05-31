<?php
// router.php para el servidor PHP embebido (Railway)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/') {
    $_GET['url'] = '';
    require __DIR__ . '/public/index.php';
    return;
}

$file = __DIR__ . '/public' . $path;
if (file_exists($file) && is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg'=> 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon'
    ];
    if (array_key_exists($ext, $mimeTypes)) {
        header("Content-Type: " . $mimeTypes[$ext]);
    }
    readfile($file);
    return;
}

$_GET['url'] = ltrim($path, '/');
require __DIR__ . '/public/index.php';
