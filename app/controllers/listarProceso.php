<?php 

header('Content-Type: application/json');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/baseHelper.php';

$helper = new baseHelper($pdo);

try {
    $procesosListados = $helper->consultObjectHelper('sp_listar_proceso_organizacional');

    if($procesosListados){
        echo json_encode([
            'status' => 'ok',
            'procesos' => $procesosListados
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