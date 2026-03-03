<?php 

header('Content-Type: application/json');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/getData.php';

try {
    $procesosListados = listarProceso($pdo);

    if($procesosListados && $procesosListados['status'] === 'ok'){
        echo json_encode([
            'status' => 'ok',
            'procesos' => $procesosListados['data']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'no se han encontrado los procesos'
        ]);
    }
} catch (Exception $e){
    error_log("error en listarProceso.php". $e->getMessage());
        echo json_encode([
        'status' => 'error',
        'mensaje' => '!Erro del servidor¡'
    ]);
}