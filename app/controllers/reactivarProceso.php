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
    $procesoData = json_decode($input, true);
    
    $id_proceso = $procesoData['id'] ?? null;
    $documento = $_SESSION['user']['documento'];
    $motivo = $procesoData['motivo'] ?? null;
    $estado = 1;
    
    if (!$id_proceso || !is_numeric($id_proceso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de proceso inválido'
        ]);
        exit;
    }

    $data = [
        ['value' => $id_proceso, 'type' => PDO::PARAM_INT],
        ['value' => $motivo, 'type' => PDO::PARAM_STR],
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $estado, 'type' => PDO::PARAM_STR],
    ];
    
    $resultado = $model->insertOrUpdateData('sp_cambiar_estado_proceso(?, ?, ?, ?)', $data);

    if ($resultado) {
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