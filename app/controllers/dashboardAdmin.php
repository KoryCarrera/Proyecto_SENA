<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

try {
    //Se llaman las funciones que necesitamos
    $casosTipos = casosPorTipo($pdo);
    $casosComisionado = casosPorComisionado($pdo);
    $casosPorMes = casosPorMes($pdo);
    
    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTipos ? $casosTipos['tipos'] : [],
        'dataPolar' => $casosTipos ? $casosTipos['casos'] : [],
        'labelsPie' => $casosComisionado ? $casosComisionado['comisionado'] : [],
        'dataPie' => $casosComisionado ? $casosComisionado['casos'] : [],
        'labelsBar' => $casosPorMes ? $casosPorMes['mes'] : [],
        'dataBar' => $casosPorMes ? $casosPorMes['casos'] : [],
        'errors' => []
    ];
    
    echo json_encode($response); //Retornamos el json
    
} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdmin.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit; //finalizamos el script
