<?php

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";

$conteo = conteoGeneral($pdo);

if (!$conteo) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Ha ocurrido un error al traer los casos'
    ]);
};

$parametros = [
    'total' => $conteo['total_casos'] ?? 0,
    'denuncias' => $conteo['total_denuncias'] ?? 0,
    'solicitudes' => $conteo['total_solicitudes'] ?? 0,
    'tutelas' => $conteo['total_acciones_tutelas'] ?? 0,
    'peticion' => $conteo['total_derechos_peticion'] ?? 0,
    'atendidos' => $conteo['total_atendidos'] ?? 0,
    'porAtender' => $conteo['total_pendientes'] ?? 0,
    'noAtendidos' => $conteo['total_no_atendidos'] ?? 0,
];

echo json_encode($parametros);