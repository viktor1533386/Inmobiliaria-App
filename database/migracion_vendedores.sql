-- Migración para añadir campos de autenticación y perfil a los vendedores
ALTER TABLE `vendedores`
ADD COLUMN `dni` VARCHAR(20) DEFAULT NULL AFTER `apellido`,
ADD COLUMN `especialidad` VARCHAR(100) DEFAULT NULL AFTER `dni`,
ADD COLUMN `linkedin` VARCHAR(255) DEFAULT NULL AFTER `especialidad`,
ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `linkedin`,
ADD COLUMN `requiere_cambio_pass` TINYINT(1) DEFAULT 0 AFTER `password`;

-- Asignar una contraseña por defecto a los vendedores existentes para evitar nulos problemáticos, aunque no la usarán a menos que el admin la resetee.
UPDATE `vendedores` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', `requiere_cambio_pass` = 1 WHERE `password` IS NULL;
