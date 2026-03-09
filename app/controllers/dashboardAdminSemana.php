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
        'labelsBar' => $casosPorSemana ? $casosPorSemana['dia'] : [],
        'dataBar' => $casosPorSemana ? $casosPorSemana['casos'] : [],
    ];

    echo json_encode($response); //Retornamos el json
    
} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdminSemana.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit; //finalizamos el script