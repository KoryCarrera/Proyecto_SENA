<?php

header('Content-Type: application/json');
// decimos que trabajaremos solo con json

// cargamos la session

session_start();

// cargamos las dependencias necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

// se toma un try catch para manejar errores  
try {
    // se crea una instancia de baseHelper
    $helper = new baseHelper($pdo);
    // se toma el documento del usuario
    $documento = $_SESSION['user']['documento'] ?? null;
    // se crea un array con el documento
    $documentData = [
        [
            'value' => $documento,
            'type' => PDO::PARAM_STR
        ]
    ];
    // se llama al metodo consultObjectWithParams
    $listarNotiComi = $helper->consultObjectWithParams("sp_listar_noti_comi(?)", $documentData);

    // se valida si se obtuvieron las notificaciones, en caso de serlo, se envia un json 
    if ($listarNotiComi) {
        echo json_encode([
            'status' => 'ok',
            'notificaciones' => $listarNotiComi
        ]);

        // en caso de no obtener las notificaciones, se envia un json con el error
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No hay notificaiones'
        ]);
    }
    // se toma el catch para manejar errores  
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarNotiComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
