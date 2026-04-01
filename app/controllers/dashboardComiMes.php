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

    $datosTiposComiMes = ['tipos' => [], 'casos' => []];
    $datosEstadoComiMes = ['estado' => [], 'casos' => [], 'total' => 0];
    $datosProcesoComiMes = ['proceso' => [], 'casos' => []];

    //Se consulta el total de casos por tipo en el mes de comisionado
    
    $casosTiposComiMes = $model->consultObjectWithParams("sp_contear_casos_tipo_mes_comi(?)", $documentData);
    
    //se valida que los datos sean correctos
    
    if ($casosTiposComiMes && count($casosTiposComiMes) > 0) {
        foreach ($casosTiposComiMes as $temp) {
            $datosTiposComiMes['tipos'][] = $temp['nombre_caso'];
            $datosTiposComiMes['casos'][] = (int) $temp['total'];
        }
    }
    
    //Se consulta el total de casos por mes del comisionado
    
    $casosEstadoComiMes = $model->consultObjectWithParams("sp_casos_por_estado_mes_comi(?)", $documentData);

    //se valida que los datos sean correctos
    
		
      if ($casosEstadoComiMes && count($casosEstadoComiMes) > 0) { 
        foreach ($casosEstadoComiMes as $temp) { 
            $datosEstadoComiMes['estado'][] = $temp['nombre_estado'];
            $datosEstadoComiMes['casos'][] = (int) $temp['total_casos'];
        }

        //Se asigna el total de casos
        
        $datosEstadoComiMes['total'] = $casosEstadoComiMes[0]['gran_total'];
    }
           
    //Se consulta el total de casos por proceso en el mes del comisionado
    
    $casosPorProcesoComiMes = $model->consultObjectWithParams("sp_casos_por_proceso_mes_comi(?)", $documentData);

    //se valida que los datos sean correctos
    
    if ($casosPorProcesoComiMes && count($casosPorProcesoComiMes) > 0) {
        foreach ($casosPorProcesoComiMes as $temp) {
            $datosProcesoComiMes['proceso'][] = $temp['proceso']; 
            $datosProcesoComiMes['casos'][] = (int) $temp['total_casos'];
        }
    }

    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    
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

    //Se retorna el json
    
    echo json_encode($response);
    exit;

    //manejo de errores con el catch 
    
} catch (Exception $e) {
    error_log("Error en dashboardComiMes.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]); 
    exit;
}