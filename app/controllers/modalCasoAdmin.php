<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {
    $idCaso = $_POST['id_caso'] ?? null;
    
    if (!$idCaso || !is_numeric($idCaso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de caso no válido'
        ]);
        exit;
    }
    
    $casoSolicitado = traerCaso($pdo, $idCaso);
    
    // Como ahora traerCaso retorna ['status' => 'ok', 'data' => {...}]
    // donde 'data' es un OBJETO, no un array
    if ($casoSolicitado && $casoSolicitado['status'] === 'ok') {
        echo json_encode([
            'status' => 'ok',
            'caso' => $casoSolicitado['data']  // Ya no necesitas [0]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Caso no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en modalCasoAdmin.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;