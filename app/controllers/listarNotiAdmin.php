<?php

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";
session_start();

try {
    $helper = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'] ?? null;
    $documentData = [
        [
            'value' => $documento,
            'type' => PDO::PARAM_STR
        ]
    ];
    
    $listarNotiAdmin = $helper->consultObjectWithParams("sp_listar_noti_admin(?)", $documentData);

    if($listarNotiAdmin){
        echo json_encode([
            'status' => 'ok',
            'notificaciones' => $listarNotiAdmin
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No hay notificaiones'
        ]);
    }
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarNotiAdmin.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
?>