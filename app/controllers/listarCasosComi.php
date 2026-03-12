<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Cargamos la session activa
session_start();

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php"; //llamamos la clase baseHelper con la conexion a la base de datos "insertData.php";

try {
    $helper = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'] ?? null;
    $documentData = [
        [
        'value' => $documento,
        'type' => PDO::PARAM_STR
        ]
    ];
	$listarCasosComi = $helper->consultObjectWithParams("sp_listar_caso_por_comisionado(?)", $documentData);
	
	if ($listarCasosComi) {
		echo json_encode([
            'status' => 'ok',
            'casos' => $listarCasosComi
        ]);
        } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $listarCasosComi ?? 'No hay casos para listar'
        ]); 
    }
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarCasosComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;

