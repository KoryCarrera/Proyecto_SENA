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



try {

    $model = new baseHelper($pdo);

    $input = file_get_contents('php://input');
    $idData = json_decode($input, true);
    
    $id_proceso = $idData['id'] ?? null;
    
    // Validar que se recibió el ID
    if (!$id_proceso || !is_numeric($id_proceso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de proceso inválido'
        ]);
        exit;
    }
    
    // Llamar a el metodo
    $data = [
        [
            'value' => $id_proceso,
            'type' => PDO::PARAM_INT
        ]
    ];


    $resultado = $model->insertOrUpdateData('sp_desactivar_proceso(?)', $data);
    if ($resultado) {
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