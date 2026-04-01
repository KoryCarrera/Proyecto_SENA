<?php

header('Content-Type: application/json');

//se indica que la respuesta y recibimiento de este script siempre será un objeto JSON.

//Se inicia la sesion

session_start();

//Se llaman los archivos con las dependencias que necesitamos

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

//Se usa try catch para manejar posibles errores de conexion a la base de datos

try {

    //Se instancia la clase baseHelper para usar sus funciones
    $model = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'];
    $documentData = [
        [
        'value' => $documento,
        'type' => PDO::PARAM_STR
        ]
    ];

    //Se declaran arrays vacios para evitar undefined variable

    $datosTiposComi = ['tipos' => [], 'casos' => []];
    $datosEstadoComi = ['estado' => [], 'casos' => [], 'total' => 0];
    $datosProcesoComi = ['proceso' => [], 'casos' => []];

    //Se consulta el total de casos por tipo de comisionado
    
    $casosTiposComi = $model->consultObjectWithParams("sp_contear_casos_tipo_comi(?)", $documentData);

    //se valida que los datos sean correctos
    
    if ($casosTiposComi && count($casosTiposComi) > 0) {
        foreach ($casosTiposComi as $temp) {
            $datosTiposComi['tipos'][] = $temp['nombre_caso'];
            $datosTiposComi['casos'][] = (int) $temp['total'];
        }
    }

    //se consulta el total de casos por estado de comisionado
    
    $casosEstadoComi = $model->consultObjectWithParams("sp_casos_por_estado_comi(?)", $documentData);

    //se valida que los datos sean correctos
    
		
      if ($casosEstadoComi && count($casosEstadoComi) > 0) { 
        foreach ($casosEstadoComi as $temp) { 
            $datosEstadoComi['estado'][] = $temp['nombre_estado'];
            $datosEstadoComi['casos'][] = (int) $temp['total_casos'];
        }
        $datosEstadoComi['total'] = $casosEstadoComi[0]['gran_total'];
    }

    //se consulta el total de casos por proceso de comisionado
    
    $casosPorProcesoComi = $model->consultObjectWithParams("sp_casos_por_proceso_comi(?)", $documentData);

    //se valida que los datos sean correctos
    
    if ($casosPorProcesoComi && count($casosPorProcesoComi) > 0) {
        foreach ($casosPorProcesoComi as $temp) {
            $datosProcesoComi['proceso'][] = $temp['proceso']; 
            $datosProcesoComi['casos'][] = (int) $temp['total_casos'];
        }
    }

    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    
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

    //Se retorna el json
    
    echo json_encode($response);
    exit;

    //manejo de errores con el catch 
    
} catch (Exception $e) {
    error_log("Error en dashboardComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]); 
        
    //Se finaliza el script

    exit;
}
    
    