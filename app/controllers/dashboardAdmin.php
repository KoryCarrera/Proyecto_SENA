<?php

header('Content-Type: application/json');
// Evitar cualquier output antes del JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);

require_once "../config/conexion.php";
require_once "../models/getData.php";

try {
    $casosTipos = casosPorTipo($pdo);
    $casosComisionado = casosPorComisionado($pdo);
    $casosPorMes = casosPorMes($pdo);
    
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
    
    if (!$casosTipos) $response['errors']['polar'] = 'No se pudieron obtener casos por tipo';
    if (!$casosComisionado) $response['errors']['pie'] = 'No se pudieron obtener casos por comisionado';
    if (!$casosPorMes) $response['errors']['bar'] = 'No se pudieron obtener casos por mes';
    
    if (!$casosTipos && !$casosComisionado && !$casosPorMes) {
        $response['status'] = 'error';
        $response['mensaje'] = 'No se pudieron obtener ningún dato';
    } else if (count($response['errors']) > 0) {
        $response['status'] = 'partial_error';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en dashboardAdmin.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit;
