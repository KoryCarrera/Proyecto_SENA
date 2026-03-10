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

    $casosTiposMes = casosPorTipoMesComi($pdo, $documento);          // Todos los casos por tipo (igual que admin)
    $casosPorEstadoMes = casosPorEstadoMesComi($pdo, $documento);    // Casos por estado (en lugar de por comisionado)
    $casosPorProcesoMes = casosPorProcesoMesComi($pdo, $documento);  // Casos por proceso (en lugar de por mes)
    
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposMes ? $casosTiposMes['tipos'] : [],
        'dataPolar' => $casosTiposMes ? $casosTiposMes['casos'] : [],
        'labelsPie' => $casosPorEstadoMes ? $casosPorEstadoMes['estado'] : [],
        'dataPie' => $casosPorEstadoMes ? $casosPorEstadoMes['casos'] : [],
        'labelsBar' => $casosPorProcesoMes ? $casosPorProcesoMes['proceso'] : [],
        'dataBar' => $casosPorProcesoMes ? $casosPorProcesoMes['casos'] : [],
        'errors' => []
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en dashboardComiMes.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
exit;