<?php

header('Content-Type: application/json');

//  se indica que se va a trabajar con json 

// se toma las variables extraidas de la sesion del usuario 

session_start();

// se incluye la conexion a la base de datos 
require_once __DIR__ . "/../config/conexion.php";
// se incluye el modelo baseHelper
require_once __DIR__ . "/../models/baseHelper.php";

// se inicia el try catch para manejar errores
try {

    // se crea una instancia de baseHelper
    $model = new baseHelper($pdo);
    // se toma el documento del usuario
    $documento = $_SESSION['user']['documento'];
    // se crea un array con el documento del usuario
    $documentData = [
        [
        'value' => $documento,
        'type' => PDO::PARAM_STR
        ]
    ];
// se declaran arrays vacios para evitar undefined variable y que su manejo sea mas sencillo 
    $datosTiposComiSemana = ['tipos' => [], 'casos' => []];
    $datosEstadoComiSemana = ['estado' => [], 'casos' => [], 'total' => 0];
    $datosProcesoComiSemana = ['proceso' => [], 'casos' => []];

    // se consulta el total de casos por tipo en la semana del comisionado
    
    $casosTiposComiSemana = $model->consultObjectWithParams("sp_contear_casos_tipo_semana_comi(?)", $documentData);
    
    // se valida que los datos sean correctos
    
    if ($casosTiposComiSemana && count($casosTiposComiSemana) > 0) {
        foreach ($casosTiposComiSemana as $temp) {
            $datosTiposComiSemana['tipos'][] = $temp['nombre_caso'];
            $datosTiposComiSemana['casos'][] = (int) $temp['total'];
        }
    }
      
    // se consulta el total de casos por estado en la semana del comisionado
    
    $casosEstadoComiSemana = $model->consultObjectWithParams("sp_casos_por_estado_semana_comi(?)", $documentData);

    // se valida que los datos sean correctos
    
		
      if ($casosEstadoComiSemana && count($casosEstadoComiSemana) > 0) { 
        foreach ($casosEstadoComiSemana as $temp) { 
            $datosEstadoComiSemana['estado'][] = $temp['nombre_estado'];
            $datosEstadoComiSemana['casos'][] = (int) $temp['total_casos'];
        }
        // se asigna el total de casos
        $datosEstadoComiSemana['total'] = $casosEstadoComiSemana[0]['gran_total'];
    }
           
    // se consulta el total de casos por proceso en la semana del comisionado
    
    $casosPorProcesoComiSemana = $model->consultObjectWithParams("sp_casos_por_proceso_semana_comi(?)", $documentData);
    
    if ($casosPorProcesoComiSemana && count($casosPorProcesoComiSemana) > 0) {
        foreach ($casosPorProcesoComiSemana as $temp) {
            $datosProcesoComiSemana['proceso'][] = $temp['proceso']; 
            $datosProcesoComiSemana['casos'][] = (int) $temp['total_casos'];
        }
    }
    // se crea la respuesta
    $response = [
        'status' => 'ok',
        // se asignan los valores a la respuesta para el front, en especial para las graficas
        'labelsPolar' => $datosTiposComiSemana['tipos'],
        'dataPolar'   => $datosTiposComiSemana['casos'],
        'labelsPie'   => $datosEstadoComiSemana['estado'],
        'dataPie'     => $datosEstadoComiSemana['casos'],
        'labelsBar'   => $datosProcesoComiSemana['proceso'],
        'dataBar'     => $datosProcesoComiSemana['casos'],
        'errors'      => []
    ];

    // se retorna el json
    
    echo json_encode($response);
    exit;

    // manejo de errores con el catch
    
} catch (Exception $e) {
    error_log("Error en dashboardComiSemana.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'No hay datos para mostrar'
    ]); 
    exit;
}