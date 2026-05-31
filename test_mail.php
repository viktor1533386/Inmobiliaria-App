<?php
define('APP_ROOT', __DIR__);
require_once __DIR__ . '/core/Mailer.php';
$res = Mailer::send('viktor16412@gmail.com', 'Prueba', 'Funciona?');
echo $res ? "OK\n" : "FAIL\n";
