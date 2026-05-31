<?php
$host = 'bdfhwiw83jhvyecnhylo-mysql.services.clever-cloud.com';
$db   = 'bdfhwiw83jhvyecnhylo';
$user = 'ujptsj5evlkffgy4';
$pass = 'mMCTnsZP4ezV1smB9lYB';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if columns exist before altering
    $stmt = $pdo->query("SHOW COLUMNS FROM `vendedores` LIKE 'dni'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `vendedores`
                ADD COLUMN `dni` VARCHAR(20) DEFAULT NULL AFTER `apellido`,
                ADD COLUMN `especialidad` VARCHAR(100) DEFAULT NULL AFTER `dni`,
                ADD COLUMN `linkedin` VARCHAR(255) DEFAULT NULL AFTER `especialidad`,
                ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `linkedin`,
                ADD COLUMN `requiere_cambio_pass` TINYINT(1) DEFAULT 0 AFTER `password`";
        $pdo->exec($sql);
        echo "Columnas agregadas con éxito.\n";
        
        $sql2 = "UPDATE `vendedores` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', `requiere_cambio_pass` = 1 WHERE `password` IS NULL";
        $pdo->exec($sql2);
        echo "Contraseñas por defecto aplicadas.\n";
    } else {
        echo "Las columnas ya existen.\n";
    }

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
