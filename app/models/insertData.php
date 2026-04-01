<?php

//FUNCIÓN: REGISTRAR CASOS

use Complex\Functions;

//FUNCIÓN: REGISTRAR UN MONITOREO
function registrarMonitoreo($pdo, $documento, $tipo, $descripcion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_monitoreo(?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR); //Isaac carechimba, el documento es string
    $stmt->bindParam9(2, $tipo, PDO::PARAM_STR);
    $stmt->bindParam(3, $descripcion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->Closecursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
        return false;
    }
}


function generarToken($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_generar_token_recuperacion(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $token;
    } catch (PDOException $e) {
        error_log("Error al generar el token: " . $e->getMessage());
        return false;
    }
}


        