<?php

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

$helper = new baseHelper($pdo);

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

    $data = [
        [ 'value' => $documento, 'type' => PDO::PARAM_STR],
    ];
    
    $usuarioSolicitado = $helper->consultSimpleWithParams('sp_traer_usuario(?)', $data);
    if ($usuarioSolicitado) {
        echo json_encode([
            'status' => 'ok',
            'usuario' => $usuarioSolicitado
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