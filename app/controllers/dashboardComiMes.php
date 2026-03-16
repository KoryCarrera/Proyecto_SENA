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

    $datosTiposComiMes = ['tipos' => [], 'casos' => []];
    $datosEstadoComiMes = ['estado' => [], 'casos' => [], 'total' => 0];
    $datosProcesoComiMes = ['proceso' => [], 'casos' => []];
    
    $casosTiposComiMes = $model->consultObjectWithParams("sp_contear_casos_tipo_mes_comi(?)", $documentData);
    
    if ($casosTiposComiMes && count($casosTiposComiMes) > 0) {
        foreach ($casosTiposComiMes as $temp) {
            $datosTiposComiMes['tipos'][] = $temp['nombre_caso'];
            $datosTiposComiMes['casos'][] = (int) $temp['total'];
        }
    }
            
    
    $casosEstadoComiMes = $model->consultObjectWithParams("sp_casos_por_estado_mes_comi(?)", $documentData);
    
		
      if ($casosEstadoComiMes && count($casosEstadoComiMes) > 0) { 
        foreach ($casosEstadoComiMes as $temp) { 
            $datosEstadoComiMes['estado'][] = $temp['nombre_estado'];
            $datosEstadoComiMes['casos'][] = (int) $temp['total_casos'];
        }
        $datosEstadoComiMes['total'] = $casosEstadoComiMes[0]['gran_total'];
    }
           
            
    $casosPorProcesoComiMes = $model->consultObjectWithParams("sp_casos_por_proceso_mes_comi(?)", $documentData);
    
    if ($casosPorProcesoComiMes && count($casosPorProcesoComiMes) > 0) {
        foreach ($casosPorProcesoComiMes as $temp) {
            $datosProcesoComiMes['proceso'][] = $temp['proceso']; 
            $datosProcesoComiMes['casos'][] = (int) $temp['total_casos'];
        }
    }
    $response = [
        'status' => 'ok',
        'labelsPolar' => $datosTiposComiMes['tipos'],
        'dataPolar'   => $datosTiposComiMes['casos'],
        'labelsPie'   => $datosEstadoComiMes['estado'],
        'dataPie'     => $datosEstadoComiMes['casos'],
        'labelsBar'   => $datosProcesoComiMes['proceso'],
        'dataBar'     => $datosProcesoComiMes['casos'],
        'errors'      => []
    ];
    
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    error_log("Error en dashboardComiMes.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]); 
    exit;
}