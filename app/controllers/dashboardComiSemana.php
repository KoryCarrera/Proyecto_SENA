<?php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

try {

    $documento = $_SESSION['user']['documento'];
    
    // El comisionado ve TODOS los casos, pero con diferentes métricas
    $casosTiposSemana = casosPorTipoSemanaComi($pdo, $documento);          // Todos los casos por tipo (igual que admin)
    $casosPorEstadoSemana = casosPorEstadoSemanaComi($pdo, $documento);    // Casos por estado (en lugar de por comisionado)
    $casosPorProcesoSemana = casosPorProcesoSemanaComi($pdo, $documento);  // Casos por proceso (en lugar de por mes)
    
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposSemana ? $casosTiposSemana['tipos'] : [],
        'dataPolar' => $casosTiposSemana ? $casosTiposSemana['casos'] : [],
        'labelsPie' => $casosPorEstadoSemana ? $casosPorEstadoSemana['estado'] : [],
        'dataPie' => $casosPorEstadoSemana ? $casosPorEstadoSemana['casos'] : [],
        'labelsBar' => $casosPorProcesoSemana ? $casosPorProcesoSemana['proceso'] : [],
        'dataBar' => $casosPorProcesoSemana ? $casosPorProcesoSemana['casos'] : [],
        'errors' => []
    ];
    
    if (!$casosTiposSemana) $response['errors']['polar'] = 'No se pudieron obtener casos por tipo';
    if (!$casosPorEstadoSemana) $response['errors']['pie'] = 'No se pudieron obtener casos por estado';
    if (!$casosPorProcesoSemana) $response['errors']['bar'] = 'No se pudieron obtener casos por proceso';
    
    if (!$casosTiposSemana && !$casosPorEstadoSemana && !$casosPorProcesoSemana) {
        $response['status'] = 'error';
        $response['mensaje'] = 'No se pudieron obtener ningún dato';
    } else if (count($response['errors']) > 0) {
        $response['status'] = 'partial_error';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en dashboardComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit;