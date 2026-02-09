<?php

function ActualizarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $password)
{
    $stmt = $pdo->prepare("CALL sp_editar_usuario(?, ?, ?, ?, ?, ?)");
/*
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(3, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(4, $email, PDO::PARAM_STR);
    $stmt->bindParam(5, $rol, PDO::PARAM_INT);
    $stmt->bindParam(6, $password, PDO::PARAM_STR);
*/
    try {
        $stmt->execute([
            $documento,
            $nombre,
            $apellido,
            $email,
            $rol,
            $password
        ]);
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("Error en SQL: " . $e->getMessage());
        return false;
    }
}

function reactivarProceso($pdo, $id_proceso){
    $stmt = $pdo->prepare("CALL sp_reactivar_proceso(?)");
    $stmt->bindParam(1, $id_proceso, PDO::PARAM_INT);

    try{
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException){
        return false;
    }
}