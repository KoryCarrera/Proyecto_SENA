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
    $stmt = $pdo->prepare("CALL sp_configurar_usuario(?, ?, ?, ?, ?, ?)");
    
    $p_documento = !empty($documento) ? $documento : null;
    $p_nombre    = !empty($nombre)    ? $nombre    : null;
    $p_apellido  = !empty($apellido)  ? $apellido  : null;
    $p_email     = !empty($email)     ? $email     : null;
    $p_numero    = !empty($numero)    ? $numero    : null;
    
    $p_passhash  = !empty($password)  ? password_hash($password, PASSWORD_BCRYPT) : null;

    $stmt->bindParam(1, $p_documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $p_nombre,    PDO::PARAM_STR);
    $stmt->bindParam(3, $p_apellido,  PDO::PARAM_STR);
    $stmt->bindParam(4, $p_email,     PDO::PARAM_STR);
    $stmt->bindParam(5, $p_passhash,  PDO::PARAM_STR);
    $stmt->bindParam(6, $p_numero,    PDO::PARAM_STR);

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
