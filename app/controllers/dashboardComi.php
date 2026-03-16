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

    $datosTiposComi = ['tipos' => [], 'casos' => []];
    $datosEstadoComi = ['estado' => [], 'casos' => [], 'total' => 0];
    $datosProcesoComi = ['proceso' => [], 'casos' => []];
    
    $casosTiposComi = $model->consultObjectWithParams("sp_contear_casos_tipo_comi(?)", $documentData);
    
    if ($casosTiposComi && count($casosTiposComi) > 0) {
        foreach ($casosTiposComi as $temp) {
            $datosTiposComi['tipos'][] = $temp['nombre_caso'];
            $datosTiposComi['casos'][] = (int) $temp['total'];
        }
    }
            
    
    $casosEstadoComi = $model->consultObjectWithParams("sp_casos_por_estado_comi(?)", $documentData);
    
		
      if ($casosEstadoComi && count($casosEstadoComi) > 0) { 
        foreach ($casosEstadoComi as $temp) { 
            $datosEstadoComi['estado'][] = $temp['nombre_estado'];
            $datosEstadoComi['casos'][] = (int) $temp['total_casos'];
        }
        $datosEstadoComi['total'] = $casosEstadoComi[0]['gran_total'];
    }
           
            
    $casosPorProcesoComi = $model->consultObjectWithParams("sp_casos_por_proceso_comi(?)", $documentData);
    
    if ($casosPorProcesoComi && count($casosPorProcesoComi) > 0) {
        foreach ($casosPorProcesoComi as $temp) {
            $datosProcesoComi['proceso'][] = $temp['proceso']; 
            $datosProcesoComi['casos'][] = (int) $temp['total_casos'];
        }
    }
    $response = [
        'status' => 'ok',
        'labelsPolar' => $datosTiposComi['tipos'],
        'dataPolar'   => $datosTiposComi['casos'],
        'labelsPie'   => $datosEstadoComi['estado'],
        'dataPie'     => $datosEstadoComi['casos'],
        'labelsBar'   => $datosProcesoComi['proceso'],
        'dataBar'     => $datosProcesoComi['casos'],
        'errors'      => []
    ];
    
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    error_log("Error en dashboardComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]); 
    exit;
}
    
    