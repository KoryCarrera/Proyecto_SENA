<?php

header('Content-Type: application/json');

session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

try {
    $model = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'];
    $documentData = [
        [
        'value' => $documento,
        'type' => PDO::PARAM_STR
        ]
    ];

    $datosTiposComiSemana = ['tipos' => [], 'casos' => []];
    $datosEstadoComiSemana = ['estado' => [], 'casos' => [], 'total' => 0];
    $datosProcesoComiSemana = ['proceso' => [], 'casos' => []];
    
    $casosTiposComiSemana = $model->consultObjectWithParams("sp_contear_casos_tipo_semana_comi(?)", $documentData);
    
    if ($casosTiposComiSemana && count($casosTiposComiSemana) > 0) {
        foreach ($casosTiposComiSemana as $temp) {
            $datosTiposComiSemana['tipos'][] = $temp['nombre_caso'];
            $datosTiposComiSemana['casos'][] = (int) $temp['total'];
        }
    }
            
    
    $casosEstadoComiSemana = $model->consultObjectWithParams("sp_casos_por_estado_semana_comi(?)", $documentData);
    
		
      if ($casosEstadoComiSemana && count($casosEstadoComiSemana) > 0) { 
        foreach ($casosEstadoComiSemana as $temp) { 
            $datosEstadoComiSemana['estado'][] = $temp['nombre_estado'];
            $datosEstadoComiSemana['casos'][] = (int) $temp['total_casos'];
        }
        $datosEstadoComiSemana['total'] = $casosEstadoComiSemana[0]['gran_total'];
    }
           
            
    $casosPorProcesoComiSemana = $model->consultObjectWithParams("sp_casos_por_proceso_semana_comi(?)", $documentData);
    
    if ($casosPorProcesoComiSemana && count($casosPorProcesoComiSemana) > 0) {
        foreach ($casosPorProcesoComiSemana as $temp) {
            $datosProcesoComiSemana['proceso'][] = $temp['proceso']; 
            $datosProcesoComiSemana['casos'][] = (int) $temp['total_casos'];
        }
    }
    $response = [
        'status' => 'ok',
        'labelsPolar' => $datosTiposComiSemana['tipos'],
        'dataPolar'   => $datosTiposComiSemana['casos'],
        'labelsPie'   => $datosEstadoComiSemana['estado'],
        'dataPie'     => $datosEstadoComiSemana['casos'],
        'labelsBar'   => $datosProcesoComiSemana['proceso'],
        'dataBar'     => $datosProcesoComiSemana['casos'],
        'errors'      => []
    ];
    
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    error_log("Error en dashboardComiSemana.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]); 
    exit;
}