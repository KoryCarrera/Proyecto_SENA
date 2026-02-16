<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {
     $nombrePost = $_POST['proceso'] ?? null;

    $nombre = trim($nombrePost);
    
    if (!$documento || !is_string($nombre)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Usuario no válido'
        ]);
        exit;
    }

    $procesoSolicitado = gestionarProceso($pdo, $nombre);

 if ($procesoSolicitado && $procesoSolicitado['status'] === 'ok') {
        echo json_encode([
            'status' => 'ok',
            'usuario' => $procesoSolicitado['data']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Proceso no encontrado'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en modalProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;   
  

    
   
?>