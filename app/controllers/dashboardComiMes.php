<?php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

try {
    // El comisionado ve TODOS los casos, pero con diferentes métricas
    $casosTiposMes = casosPorTipoMesComi($pdo);          // Todos los casos por tipo (igual que admin)
    $casosPorEstado = casosPorEstado($pdo);    // Casos por estado (en lugar de por comisionado)
    $casosPorProceso = casosPorProceso($pdo);  // Casos por proceso (en lugar de por mes)
    
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposMes ? $casosTiposMes['tipos'] : [],
        'dataPolar' => $casosTiposMes ? $casosTiposMes['casos'] : [],
        'labelsPie' => $casosPorEstado ? $casosPorEstado['estado'] : [],
        'dataPie' => $casosPorEstado ? $casosPorEstado['casos'] : [],
        'labelsBar' => $casosPorProceso ? $casosPorProceso['proceso'] : [],
        'dataBar' => $casosPorProceso ? $casosPorProceso['casos'] : [],
        'errors' => []
    ];
    
    if (!$casosTiposMes) $response['errors']['polar'] = 'No se pudieron obtener casos por tipo';
    if (!$casosPorEstado) $response['errors']['pie'] = 'No se pudieron obtener casos por estado';
    if (!$casosPorProceso) $response['errors']['bar'] = 'No se pudieron obtener casos por proceso';
    
    if (!$casosTiposMes && !$casosPorEstado && !$casosPorProceso) {
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