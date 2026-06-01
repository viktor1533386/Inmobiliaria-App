<?php
// Script para actualizar la base de datos de Clever Cloud a la nueva estructura de roles
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();
    echo "<h1>Actualizando Base de Datos en Clever Cloud...</h1>";

    // 1. Actualizar tabla usuarios
    $sqlUsuarios = "
        ALTER TABLE `usuarios` 
        ADD COLUMN `rol` ENUM('admin', 'supervisor', 'vendedor') NOT NULL DEFAULT 'supervisor' AFTER `password`,
        ADD COLUMN `estado` TINYINT(1) DEFAULT 1 AFTER `rol`,
        ADD COLUMN `password_reset_required` TINYINT(1) DEFAULT 1 AFTER `estado`;
    ";
    
    try {
        $db->query($sqlUsuarios);
        echo "<p>✅ Tabla 'usuarios' actualizada correctamente (se agregaron campos rol, estado, password_reset_required).</p>";
        
        // Actualizar el admin actual para que no requiera reset
        $db->query("UPDATE `usuarios` SET `rol` = 'admin', `password_reset_required` = 0 WHERE `id` = 1");
    } catch (Exception $e) {
        echo "<p>⚠️ Nota: La tabla 'usuarios' tal vez ya estaba actualizada. (" . $e->getMessage() . ")</p>";
    }

    // 2. Actualizar tabla vendedores
    $sqlVendedores1 = "
        ALTER TABLE `vendedores`
        ADD COLUMN `usuario_id` INT UNSIGNED AFTER `id`;
    ";
    $sqlVendedores2 = "
        ALTER TABLE `vendedores`
        DROP COLUMN `password`,
        DROP COLUMN `requiere_cambio_pass`;
    ";
    $sqlVendedores3 = "
        ALTER TABLE `vendedores`
        ADD CONSTRAINT `fk_vendedor_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE;
    ";

    try {
        $db->query($sqlVendedores1);
        echo "<p>✅ Tabla 'vendedores': se agregó 'usuario_id'.</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Nota: Columna usuario_id ya existía. (" . $e->getMessage() . ")</p>";
    }

    try {
        $db->query($sqlVendedores2);
        echo "<p>✅ Tabla 'vendedores': se eliminaron las contraseñas antiguas.</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Nota: Las contraseñas antiguas ya fueron eliminadas. (" . $e->getMessage() . ")</p>";
    }

    try {
        $db->query($sqlVendedores3);
        echo "<p>✅ Tabla 'vendedores': se creó la llave foránea con 'usuarios'.</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Nota: Llave foránea ya existe. (" . $e->getMessage() . ")</p>";
    }

    echo "<h2 style='color:green'>¡Actualización finalizada con éxito!</h2>";
    echo "<p>Por seguridad, te recomendamos eliminar este archivo (actualizar_bd.php) luego de usarlo.</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Error Crítico:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
