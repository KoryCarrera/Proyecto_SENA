<?php

function ActualizarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $password)
{
    $stmt = $pdo->prepare("CALL sp_editar_usuario(?, ?, ?, ?, ?, ?)");
    //encriptar contraseña
    $passhash = password_hash($password, PASSWORD_BCRYPT);

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
    } catch (PDOException $e) {
        error_log("Error en SQL: " . $e->getMessage());
        return false;
    }
}

function ConfigurarInfoUsuario($pdo, $documento, $nombre, $apellido, $email, $password, $numero)
{
    $stmt = $pdo->prepare("CALL sp_configurar_usuario(?, ?, ?, ?, ?; ?)");
    //encriptar contraseña
    $passhash = password_hash($password, PASSWORD_BCRYPT);

    $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(2, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(3, $email, PDO::PARAM_STR);
    $stmt->bindParam(4, $passhash, PDO::PARAM_STR);
    $stmt->bindParam(5, $documento, PDO::PARAM_STR);
    $stmt->bindParam(6, $numero, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("Error en SQL: " . $e->getMessage());
        return false;
    }
}

function reactivarProceso($pdo, $id_proceso)
{
    $stmt = $pdo->prepare("CALL sp_reactivar_proceso(?)");
    $stmt->bindParam(1, $id_proceso, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException) {
        return false;
    }
}

function actualizarEstadoCaso($pdo, $idCaso, $idEstado, $documento)
{
    $stmt = $pdo->prepare("CALL sp_actualizar_estado_caso(?, ?, ?)");
    $stmt->bindParam(1, $idCaso, PDO::PARAM_INT);
    $stmt->bindParam(2, $idEstado, PDO::PARAM_INT);
    $stmt->bindParam(3, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("Error en actualizarEstadoCaso: " . $e->getMessage());
        return false;
    }
}
function cambiarContraseña($pdo, $token, $password_hash)
{
    $stmt = $pdo->prepare("CALL sp_cambiar_contrasena_con_token(?, ?)");
    $stmt->blindParam(1, $token, PDO::PARAM_STR);
    $stmt->blindParam(2, $password_hash, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e){
        error_log("Error al cambiar la contraseña: ". $e->getMessage());
        return false;
    }
}
