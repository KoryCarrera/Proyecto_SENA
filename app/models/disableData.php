<?php

function inhabilitarUsuario($pdo, $document)
{
    $stmt = $pdo->prepare("CALL sp_deshabilitar_usuario(?)");
    $stmt->bindParam(1, $document, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException) {
        return false;
    }
}

function desactivarProceso($pdo, $id_proceso){
    $stmt = $pdo->prepare("CALL sp_desactivar_proceso(?)");
    $stmt->bindParam(1, $id_proceso, PDO::PARAM_INT);

    try{
        $stmt->execute();
        $stmt->closeCursor();
        return true;
    } catch (PDOException){
        return false;
    }
}
