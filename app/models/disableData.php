<?php

function cambiarEstadoUsuario($pdo, $document, $nuevoEstado)
{
    $stmt = $pdo->prepare("CALL sp_cambiar_estado_usuario(?, ?)");
    $stmt->bindParam(1, $document, PDO::PARAM_STR);
    $stmt->bindParam(2, $nuevoEstado, PDO::PARAM_INT);

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
