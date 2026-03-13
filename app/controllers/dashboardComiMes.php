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
    // El comisionado ve TODOS los casos, pero con diferentes métricas

    $casosTipoMesComi = $model->consultObjectWithParams("sp_contear_casos_tipo_mes_comi(?)");, $documentData);
    
    if ($casosTipoMesComi && count($casosTipoMesComi) >= 0) {
        $nombres = [];
        $totales = [];

        foreach ($casosTipoMesComi as $temp) {
            $nombres[] = $temp['nombre_caso']; 
            $totales[] = (int) $temp['total'];   
        }
        $casosTipoMesComi = [
            'tipos' => $nombres,
            'casos' => $totales
        ];
    }
    $casosEstadoMesComi = $model->consultObjectWithParams("sp_casos_por_estado_mes_comi(?)");, $documentData);
    
		
      if ($casosEstadoMesComi && count($casosEstadoMesComi) > 0) { 
            $estados = [];
            $casos = [];

            foreach ($casosEstadoMesComi as $temp) { 
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstadoMesComi[0]['gran_total']
            ];
            
    $casosPorProcesoMesComi = c$model->consultObjectWithParams("sp_casos_por_proceso_mes_comi(?)");, $documentData);
    
    if ($casosPorProcesoMesComi && count($casosPorProcesoMesComi) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosPorProcesoMesComi as $temp) {
                $proceso[] = $temp['proceso'];          
                $casos[] = (int) $temp['total_casos'];    
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
    
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTipoMesComi ? $casosTipoMesComi['tipos'] : [],
        'dataPolar' => $casosTipoMesComi ? $casosTipoMesComi['casos'] : [],
        'labelsPie' => $casosEstadoMesComi ? $casosEstadoMesComi['estado'] : [],
        'dataPie' => $casosEstadoMesComi ? $casosEstadoMesComi['casos'] : [],
        'labelsBar' => $casosPorProcesoMesComi ? $casosPorProcesoMesComi['proceso'] : [],
        'dataBar' => $casosPorProcesoMesComi ? $casosPorProcesoMesComi['casos'] : [],
        'errors' => []
    ];
    
    echo json_encode($response);
    
}
}
} catch (Exception $e) {
    error_log("Error en dashboardComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'No hay casos por mostrar: ' . $e->getMessage()
	};
	exit;
    
