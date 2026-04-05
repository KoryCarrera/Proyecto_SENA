<?php
require_once __DIR__ . '/app/config/conexion.php';

$sql = "DROP PROCEDURE IF EXISTS `sp_listar_archivos_caso`;
CREATE PROCEDURE `sp_listar_archivos_caso` (IN `p_id_caso` INT)
BEGIN
    SELECT id_archivo, nombre_archivo, ruta, tipo_archivo, fecha_subida 
    FROM archivo 
    WHERE id_caso = p_id_caso 
    ORDER BY fecha_subida DESC;
END;";

try {
    $pdo->exec($sql);
    echo "Procedimiento sp_listar_archivos_caso creado/actualizado exitosamente.";
} catch (PDOException $e) {
    echo "Error al crear el procedimiento: " . $e->getMessage();
}
unlink(__FILE__); // Autodestruirse
