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
    
    $casosTiposComi = $model->consultObjectWithParams("sp_contear_casos_tipo_comi(?)");, $documentData);
    
    if ($casosTiposComi && count($casosTiposComi >= 0) {
        $nombres = [];
        $totales = [];

        foreach ($casosTiposComi as $temp) {
            $nombres[] = $temp['nombre_caso']; 
            $totales[] = (int) $temp['total'];   
        }
        $casosTiposComi = [
            'tipos' => $nombres,
            'casos' => $totales
        ];
    }
    
    $casosEstadoComi = $model->consultObjectWithParams("sp_casos_por_estado_comi(?)"), $documentData);
    
		
      if ($casosEstadoComi && count($casosEstadoComi) > 0) {
            $estados = [];
            $casos = [];

            foreach ($casosEstadoComi as $temp) { 
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstadoComi[0]['gran_total']
            ];
            
    $casosPorProcesoComi = $model->consultObjectWithParams("sp_casos_por_proceso_comi(?)");, $documentData);
    
    if ($casosPorProcesoComi && count($casosPorProcesoComi > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosPorProcesoComi as $temp) {
                $proceso[] = $temp['proceso'];          
                $casos[] = (int) $temp['total_casos'];    
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
    
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposComi ? $casosTiposComi['tipos'] : [],
        'dataPolar' => $casosTiposComi ? $casosTiposComi['casos'] : [],
        'labelsPie' => $casosEstadoComi ? $casosEstadoComi['estado'] : [],
        'dataPie' => $casosEstadoComi ? $casosEstadoComi['casos'] : [],
        'labelsBar' => $casosPorProcesoComi ? $casosPorProcesoComi['proceso'] : [],
        'dataBar' => $casosPorProcesoComi ? $casosPorProcesoComi['casos'] : [],
        'errors' => []
    ];
    
    echo json_encode($response);
    
} 
    ]);
}
	} catch (Exception $e) {
    error_log("Error en dashboardComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'No hay casos por mostrar: ' . $e->getMessage()
};
exit;
	
	
	
