<?php

// Indicamos que la respuesta del controlador será en formato JSON
header('Content-Type: application/json');

// Inclusión de dependencias

// Archivo de conexión a la base de datos (PDO)
require_once __DIR__ . "/../config/conexion.php";

// Modelo que contiene las funciones para obtener los catálogos
require_once __DIR__ . "/../models/getData.php";


// Este controlador solo acepta peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit; // Detiene la ejecución del script
}

try {

    // Obtiene la lista de procesos activos
    $procesos = listarProcesosActivos($pdo);

    // Obtiene los tipos de caso disponibles
    $tiposCaso = listarTiposCaso($pdo);

    // Obtiene los estados posibles del caso
    $estadosCaso = listarEstadosCaso($pdo);


    // Si alguno de los catálogos falla o no retorna datos
    if (!$procesos || !$tiposCaso || !$estadosCaso) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al cargar los catálogos'
        ]);
        exit;
    }


    // Se retorna toda la información en una sola respuesta
    echo json_encode([
        'status' => 'ok',
        'data' => [
            'procesos'    => $procesos['data'],
            'tiposCaso'   => $tiposCaso['data'],
            'estadosCaso' => $estadosCaso['data']
        ]
    ]);

} catch (Exception $e) {

    // Se registra el error en el log del servidor
    error_log("Error en listarCatalogosCaso.php: " . $e->getMessage());

    // Mensaje genérico al cliente (no expone detalles internos)
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
