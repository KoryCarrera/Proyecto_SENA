<?php

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

// se valida que el metodo sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

// se crea una instancia del modelo de casos
$helper = new baseHelper($pdo);

try {
    // se obtiene el documento del usuario
    $documentoPost = $_POST['usuario'] ?? null;

    // se valida que el documento sea valido
    $documento = trim($documentoPost);
    
    // si el documento no es valido, muestra el error
    if (!$documento || !is_string($documento)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Usuario no válido'
        ]);
        exit;
    }

    // se crea un array con el documento del usuario
    $data = [
        [ 'value' => $documento, 'type' => PDO::PARAM_STR],
    ];
    // se llama al metodo consultSimpleWithParams
    $usuarioSolicitado = $helper->consultSimpleWithParams('sp_traer_usuario(?)', $data);
    // se valida si el usuario fue encontrado
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