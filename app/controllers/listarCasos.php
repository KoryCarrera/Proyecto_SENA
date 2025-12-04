<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";

try {
    $casosListados = listarCasos($pdo);
    
    if ($casosListados && $casosListados['status'] === 'ok') {
        echo json_encode([
            'status' => 'ok',
            'casos' => $casosListados['data']  // Acceder a ['data']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $casosListados['mensaje'] ?? 'No hay casos para mostrar'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en listarCasos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;