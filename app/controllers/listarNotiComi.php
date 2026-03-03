<?php

header('Content-Type: application/json');

session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";


$documento = $_SESSION['user']['documento'];

try {
    $listarNotiComi = listarNotiComi($pdo, $documento);

    if($listarNotiComi && $listarNotiComi['status'] === 'ok'){
        echo json_encode([
            'status' => 'ok',
            'notificaciones' => $listarNotiComi['data']
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
?>