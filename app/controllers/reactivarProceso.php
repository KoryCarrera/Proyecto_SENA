<?php 

header('Content-Type: application/json');

// se indica que solo se trabajara con json

//Se inicializa la session
session_start();

//se incluyen las dependencias necesarias

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

    // se crea una instancia del modelo de casos
    $model = new baseHelper($pdo);

    // se obtiene el documento del usuario
    $input = file_get_contents('php://input');
    $procesoData = json_decode($input, true);

    // se obtiene el id del proceso
    $id_proceso = $procesoData['id'] ?? null;
    // se obtiene el documento del usuario
    $documento = $_SESSION['user']['documento'];
    // se obtiene el motivo
    $motivo = $procesoData['motivo'] ?? null;
    // se define el estado 
    $estado = 1;
    
    // se valida que el id del proceso sea valido
    if (!$id_proceso || !is_numeric($id_proceso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de proceso inválido'
        ]);
        exit;
    }

    // se crea un array con los datos del proceso
    $data = [
        ['value' => $id_proceso, 'type' => PDO::PARAM_INT],
        ['value' => $motivo, 'type' => PDO::PARAM_STR],
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $estado, 'type' => PDO::PARAM_STR],
    ];
    
    // se llama al metodo insertOrUpdateData
    $resultado = $model->insertOrUpdateData('sp_cambiar_estado_proceso(?, ?, ?, ?)', $data);

    // se valida si el proceso se reactivo exitosamente
    if ($resultado) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso reactivado exitosamente'
        ]);
        // si no se pudo reactivar el proceso, muestra el error
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