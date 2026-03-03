<?php 

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/updateData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',  
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id_proceso = $data['id'] ?? null;
    
    if (!$id_proceso || !is_numeric($id_proceso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de proceso inválido'
        ]);
        exit;
    }
    
    $resultado = reactivarProceso($pdo, $id_proceso);

    if ($resultado === true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso reactivado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se pudo reactivar el proceso'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en reactivarProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}