<?php
header('Content-Type: application/json');

require_once __DIR__ . "/checksession.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {

    $procesos    = listarProcesosActivos($pdo);
    $tiposCaso   = listarTiposCaso($pdo);
    $estadosCaso = listarEstadosCaso($pdo);

    if (!$procesos || !$tiposCaso || !$estadosCaso) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al cargar los catálogos'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'ok',
        'data' => [
            'procesos'    => $procesos['data'],
            'tiposCaso'   => $tiposCaso['data'],
            'estadosCaso' => $estadosCaso['data']
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en listarCatalogosCaso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
