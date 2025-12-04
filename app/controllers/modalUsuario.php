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
    $documentoPost = $_POST['usuario'] ?? null;

    $documento = trim($documentoPost);
    
    if (!$documento || !is_string($documento)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Usuario no válido'
        ]);
        exit;
    }
    
    $usuarioSolicitado = gestionarUsuario($pdo, $documento);

    if ($usuarioSolicitado && $usuarioSolicitado['status'] === 'ok') {
        echo json_encode([
            'status' => 'ok',
            'usuario' => $usuarioSolicitado['data']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Usuario no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en modalUsuario.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;