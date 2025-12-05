<?php 

//FUNCIÓN: REGISTRAR CASOS
function registrarCasos($pdo, $documento, $proceso, $estado, $tipo, $descripcion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_caso(?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_INT);
    $stmt->bindParam(2, $proceso, PDO::PARAM_INT);
    $stmt->bindParam(3, $estado, PDO::PARAM_INT);
    $stmt->bindParam(4, $tipo, PDO::PARAM_INT);
    $stmt->bindParam(5, $descripcion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
        return false;
    }
}

//FUNCIÓN: REGISTRAR UN SEGUIMIENTO
function registrarSeguimiento($pdo, $observacion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_seguimiento(?)");
    $stmt->bindParam(1, $observacion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
        return false;
    }
}

//FUNCIÓN: REGISTRAR UN NUEVO USUARIO (LLAMADA A sp_registrar_usuario)
function registrarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $contraseña)
{
    // GENERACIÓN DEL HASH SEGURO DE LA CONTRASEÑA
    $passhash = password_hash($contraseña, PASSWORD_BCRYPT);

    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_usuario(?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(3, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(4, $email, PDO::PARAM_STR);
    $stmt->bindParam(5, $rol, PDO::PARAM_INT);
    $stmt->bindParam(6, $passhash, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
        return false;
    }
}

//FUNCIÓN: REGISTRAR UN MONITOREO
function registrarMonitoreo($pdo, $documento, $tipo, $descripcion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_monitoreo(?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_INT);
    $stmt->bindParam9(2, $tipo, PDO::PARAM_STR);
    $stmt->bindParam(3, $descripcion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->Closecursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch(PDOException) {
        return false;
    }
}
?>