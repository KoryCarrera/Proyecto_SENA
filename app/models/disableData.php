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
