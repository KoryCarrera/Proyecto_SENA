<?php 

header('Content-Type: application/json');
 // se indica que se va a trabajar con json 

//Se inicializa la session
session_start();

// se incluye la conexion a la base de datos 
require_once __DIR__ . "/../config/conexion.php";
// se incluye el modelo baseHelper
require_once __DIR__ . "/../models/baseHelper.php";

// se valida que el metodo sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',  
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}
// se toma un try catch para manejar errores 
try {
    // se crea una instancia de baseHelper
    $model = new baseHelper($pdo);

    // se toma el input del request de js 
    $input = file_get_contents('php://input');
    // se decodifica el input y se envia en un json 
    $procesoData = json_decode($input, true);
    
    // se toman los datos del input
    $id_proceso = $procesoData['id'] ?? null;
    $documento = $_SESSION['user']['documento'];
    $motivo = $procesoData['motivo'] ?? null;
    $estado = 0;
    
    // Validar que se recibió el ID
    if (!$id_proceso || !is_numeric($id_proceso)) {

        // se envia un json con el error
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de proceso inválido'
        ]);
        exit;
    }
    
    // Llamar a el metodo
    $data = [
        ['value' => $id_proceso, 'type' => PDO::PARAM_INT],
        ['value' => $motivo, 'type' => PDO::PARAM_STR],
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $estado, 'type' => PDO::PARAM_STR],
    ];

    // se llama al metodo insertOrUpdateData
    $resultado = $model->insertOrUpdateData('sp_cambiar_estado_proceso(?, ?, ?, ?)', $data);
    if ($resultado) {
        // se envia un json para el mensaje exitoso
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso desactivado exitosamente'
        ]);
    } else {
        // se envia un json con el error
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se pudo desactivar el proceso'
        ]);
    }
    
    // se toma el catch para manejar errores  
} catch (Exception $e) {
    error_log("Error en desactivarProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}