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
    
        $casosTipoSemanaComi = $model->consultObjectWithParams("sp_contear_casos_tipo_semana_comi(?)");, $documentData);
    
    if ($casosTipoSemanaComi && count($casosTipoSemanaComi) >= 0) {
        $nombres = [];
        $totales = [];

        foreach ($casosTipoSemanaComi as $temp) {
            $nombres[] = $temp['nombre_caso']; 
            $totales[] = (int) $temp['total'];   
        }
        $casosTipoSemanaComi = [
            'tipos' => $nombres,
            'casos' => $totales
        ];
    }
    
    $casosEstadoSemanaComi = $model->consultObjectWithParams("sp_casos_por_estado_semana_comi(?)");, $documentData);
    
		
      if ($casosEstadoSemanaComi && count($casosEstadoSemanaComi) > 0) { 
            $estados = [];
            $casos = [];

            foreach ($casosEstadoSemanaComi as $temp) { 
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstadoSemanaComi[0]['gran_total']
            ];
    $casosPorProcesoSemanaComi = $model->consultObjectWithParams("sp_casos_por_proceso_semana_comi(?)");, $documentData);
    
    if ($casosProcesoSemanaComi && count($casosProcesoSemanaComi) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProcesoSemanaComi as $temp) {
                $proceso[] = $temp['proceso'];          
                $casos[] = (int) $temp['total_casos'];    
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
    
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTipoSemanaComi ? $casosTipoSemanaComi['tipos'] : [],
        'dataPolar' => $casosTipoSemanaComi ? $casosTipoSemanaComi['casos'] : [],
        'labelsPie' => $casosEstadoSemanaComi ? $casosEstadoSemanaComi['estado'] : [],
        'dataPie' => $casosEstadoSemanaComi ? $casosEstadoSemanaComi['casos'] : [],
        'labelsBar' => $casosPorProcesoSemanaComi ? $casosPorProcesoSemanaComi['proceso'] : [],
        'dataBar' => $casosPorProcesoSemanaComi ? $casosPorProcesoSemanaComi['casos'] : [],
        'errors' => []
    ];
    
	}catch (Exception $e) {
    error_log("Error en dashboardComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]); 
    };
exit;
	
