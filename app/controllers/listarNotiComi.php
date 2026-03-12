<?php

header('Content-Type: application/json');

session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

try {
    $helper = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'] ?? null;
    $documentData = [
        [
            'value' => $documento,
            'type' => PDO::PARAM_STR
        ]
    ];
    $listarNotiComi = $helper->consultObjectWithParams("sp_listar_noti_comi(?)", $documentData);

    if ($listarNotiComi) {
        echo json_encode([
            'status' => 'ok',
            'notificaciones' => $listarNotiComi
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No hay notificaiones'
        ]);
    }
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarNotiComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
