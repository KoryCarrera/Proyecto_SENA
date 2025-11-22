<?php 

require_once '../config/conexion.php';

function registrarCasos($pdo, $documento, $proceso, $estado, $tipo, $descripcion)
{
    
    $stmt = $pdo->prepare("CALL sp_registrar_caso(:documento, :proceso, :estado, :tipo, :descripción)");
    $stmt->bindParam(':documento', $documento, PDO::PARAM_INT);
    $stmt->bindParam(':proceso', $proceso, PDO::PARAM_INT);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_INT);
    $stmt->bindParam(':descripción', $descripcion, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException) {
        return false;
    }

}

function registrarSeguimiento($pdo, $observacion)
{
    $stmt = $pdo->prepare("CALL sp_registrar_seguimiento(:observacion)");
    $stmt->bindParam(':observacion', $observacion, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException) {
        return false;
    }
}

function registrarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $contraseña)
{
    $stmt = $pdo->prepare("CALL sp_registrar_usuario(:documento, :nombre, :apellido, :email, :rol, :contraseña)");
    $stmt->bindParam(':documento', $documento, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':rol', $rol, PDO::PARAM_INT);
    $stmt->bindParam(':contraseña', $contraseña, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException) {
        return false;
    }

}

function registrarMonitoreo($pdo, $documento, $tipo, $descripcion)
{
    $stmt = $pdo->prepare("CALL sp_registrar_monitoreo(:documento, :tipo, :descripcion)");
    $stmt->bindParam(':documento', $documento, PDO::PARAM_INT);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->Closecursor();
        return true;
    } catch(PDOException) {
        return false;
    }
}
?>