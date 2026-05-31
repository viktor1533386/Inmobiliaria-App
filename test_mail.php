<?php
define('APP_ROOT', __DIR__);
require_once __DIR__ . '/core/Mailer.php';
$res = Mailer::send('viktor16412@gmail.com', 'Prueba API Brevo (Railway Bypass)', '<h1>¡Funciona!</h1><p>El bloqueo SMTP ha sido superado usando el puerto 443 HTTPS.</p>');
echo $res ? "OK\n" : "FAIL\n";
