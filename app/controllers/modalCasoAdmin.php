<?php
//Especificacion del tipo de comunicacion de este script
header('Content-Type: application/json');

//Llamamos los archivos necesarios
require_once "../config/conexion.php";
require_once "../models/getData.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') { //Validamos que es el metodo que necesitamos
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

//Abrimos un try para empezar la ejecucion
try {
    $idCaso = $_POST['id_caso'] ?? null; 
    
    if (!$idCaso || !is_numeric($idCaso)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de caso no válido'
        ]);
        exit;
    }
    
    $casoSolicitado = traerCaso($pdo, $idCaso); //Llamamos la funcion que trae unicamente el caso que necesitamos
    

    if ($casoSolicitado && $casoSolicitado['status'] === 'ok') { // Validamos el status y si es true
        echo json_encode([
            'status' => 'ok',
            'caso' => $casoSolicitado['data']  //Enviamos con JSON la data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Caso no encontrado'
        ]);
    }
    
} catch (Exception $e) { //manejo de errores sql 
    error_log("Error en modalCasoAdmin.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;