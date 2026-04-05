<?php 

header('Content-Type: application/json');
// decimos que trabajaremos solo con json

// cargamos las dependencias necesarias
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/baseHelper.php';

// se crea una instancia de baseHelper
$helper = new baseHelper($pdo);

try {
    // se llama al metodo consultObjectHelper
    $procesosListados = $helper->consultObjectHelper('sp_listar_proceso_organizacional');

    // se valida si se obtuvieron los procesos, en caso de serlo, se envia un json 
    if($procesosListados){
        echo json_encode([
            'status' => 'ok',
            'procesos' => $procesosListados
        ]);

        // en caso de no obtener los procesos, se envia un json con el error
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'no se han encontrado los procesos'
        ]);
    }
    // se toma el catch para manejar errores  
} catch (Exception $e){
    error_log("error en listarProceso.php". $e->getMessage());
        echo json_encode([
        'status' => 'error',
        'mensaje' => '!Erro del servidor¡'
    ]);
}