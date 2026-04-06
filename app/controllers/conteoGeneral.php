<?php

header('Content-Type: application/json');

//se define el formato de respuesta y peticion de con la que trabajamos (json)

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

//llamamos las dependencias necesarias para el funcionamiento del controlador

//se crea una instancia de la clase baseHelper

$helper = new baseHelper($pdo);

//se valida que el metodo sea GET 

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    echo json_encode([
        'status' => 'Error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
}

//se consulta el total de casos

$conteo = $helper->consultSimpleHelper('sp_resumen_casos_global');

//se valida que no sea nulo el conteo de casos

if (!$conteo) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Ha ocurrido un error al traer los casos'
    ]);
    exit;
};

//se crea un aray con la separacion por datos para su posterior uso de manera sencilla

$parametros = [
    'total' => $conteo['total_casos'] ?? 0,
    'denuncias' => $conteo['total_denuncias'] ?? 0,
    'solicitudes' => $conteo['total_solicitudes'] ?? 0,
    'tutelas' => $conteo['total_acciones_tutela'] ?? 0,
    'peticion' => $conteo['total_derechos_peticion'] ?? 0,
    'atendidos' => $conteo['total_atendidos'] ?? 0,
    'porAtender' => $conteo['total_pendientes'] ?? 0,
    'noAtendidos' => $conteo['total_no_atendidos'] ?? 0,
    'porAsignar' => $conteo['total_por_asignar'] ?? 0,
];

echo json_encode($parametros);