<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

try {
    //Se llaman las funciones que necesitamos
    $casosTiposMes = casosPorTipoMes($pdo);
    $casosComisionadoMes = casosPorComisionadoMes($pdo);
    $casosPorUnMes = casosPorUnMes($pdo);
    
    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposMes ? $casosTiposMes['tipos'] : [],
        'dataPolar' => $casosTiposMes ? $casosTiposMes['casos'] : [],
        'labelsPie' => $casosComisionadoMes ? $casosComisionadoMes['comisionado'] : [],
        'dataPie' => $casosComisionadoMes ? $casosComisionadoMes['casos'] : [],
        'labelsBar' => $casosPorUnMes ? $casosPorUnMes['dia'] : [],
        'dataBar' => $casosPorUnMes ? $casosPorUnMes['casos'] : [],
        'errors' => []
    ];
    
    
    echo json_encode($response); //Retornamos el json
    
} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdminMes.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit; //finalizamos el script