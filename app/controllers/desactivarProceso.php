<?php 

header('Content-Type: application/json');

require_once __DIR__ . "/checksession.php";  
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/disableData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',  
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {
    // CAPTURAR DATOS JSON 
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id_proceso = $data['id'] ?? null;
    
    // Validar que se recibió el ID
    if (!$id_proceso || !is_numeric($id_proceso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de proceso inválido'
        ]);
        exit;
    }
    
    // Llamar a la función 
    $resultado = desactivarProceso($pdo, $id_proceso);

    if ($resultado === true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso desactivado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se pudo desactivar el proceso'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en desactivarProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}