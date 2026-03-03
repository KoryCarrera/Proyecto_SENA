<?php

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

try {
    $listarNotiAdmin = listarNotiAdmin($pdo);

    if($listarNotiAdmin && $listarNotiAdmin['status'] === 'ok'){
        echo json_encode([
            'status' => 'ok',
            'notificaciones' => $listarNotiAdmin['data']
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