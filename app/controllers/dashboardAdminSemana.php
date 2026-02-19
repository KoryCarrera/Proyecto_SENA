<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

try {
    //Se llaman las funciones que necesitamos
    $casosTiposSemana = casosPorTipoSemana($pdo);
    $casosComisionadoSemana = casosPorComisionadoSemana($pdo);
    $casosPorSemana = casosPorSemana($pdo);
    
    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposSemana ? $casosTiposSemana['tipos'] : [],
        'dataPolar' => $casosTiposSemana ? $casosTiposSemana['casos'] : [],
        'labelsPie' => $casosComisionadoSemana ? $casosComisionadoSemana['comisionado'] : [],
        'dataPie' => $casosComisionadoSemana ? $casosComisionadoSemana['casos'] : [],
        'labelsBar' => $casosPorSemana ? $casosPorSemana['dia_semana'] : [],
        'dataBar' => $casosPorSemana ? $casosPorSemana['casos_dia'] : [],
        'errors' => []
    ];
    
    //Se validan que los datos devueltos no sean false por cada uno
    if (!$casosTiposSemana) $response['errors']['polar'] = 'No se pudieron obtener casos por tipo en este mes';
    if (!$casosComisionadoSemana) $response['errors']['pie'] = 'No se pudieron obtener casos por comisionado en este mes';
    if (!$casosPorSemana) $response['errors']['bar'] = 'No se pudieron obtener casos por este mes';
    
    if (!$casosTiposSemana && !$casosComisionadoSemana && !$casosPorSemana) { //Validamos que todos esten llenos mediante una negación
        $response['status'] = 'error';
        $response['mensaje'] = 'No se pudieron obtener ningún dato';
    } else if (count($response['errors']) > 0) { //Validamos que no hayan errores
        $response['status'] = 'partial_error';
    }
    
    echo json_encode($response); //Retornamos el json
    
} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdminSemana.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit; //finalizamos el script