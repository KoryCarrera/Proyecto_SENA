<?php 

function registrarCasos($pdo, $documento, $proceso, $estado, $tipo, $descripcion)
{
    
    $stmt = $pdo->prepare("CALL sp_registrar_caso(?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_INT);
    $stmt->bindParam(2, $proceso, PDO::PARAM_INT);
    $stmt->bindParam(3, $estado, PDO::PARAM_INT);
    $stmt->bindParam(4, $tipo, PDO::PARAM_INT);
    $stmt->bindParam(5, $descripcion, PDO::PARAM_STR);

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
    $passhash = password_hash($contraseña, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("CALL sp_registrar_usuario(?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(3, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(4, $email, PDO::PARAM_STR);
    $stmt->bindParam(5, $rol, PDO::PARAM_INT);
    $stmt->bindParam(6, $passhash, PDO::PARAM_STR);

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