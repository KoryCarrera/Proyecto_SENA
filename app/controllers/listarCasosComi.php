<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Cargamos la session activa
session_start();

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php"; //llamamos la clase baseHelper con la conexion a la base de datos "insertData.php";

// se toma un try catch para manejar errores  
try {
    // se crea una instancia de baseHelper
    $helper = new baseHelper($pdo);
    // se toma el documento del usuario
    $documento = $_SESSION['user']['documento'] ?? null;
    // se crea un array con el documento
    $documentData = [
        [
        'value' => $documento,
        'type' => PDO::PARAM_STR
        ]
    ];
    // se llama al metodo consultObjectWithParams
	$listarCasosComi = $helper->consultObjectWithParams("sp_listar_caso_por_comisionado(?)", $documentData);
	

    // se valida si se obtuvieron los casos, en caso de serlo, se envia un json 
	if ($listarCasosComi) {
		echo json_encode([
            'status' => 'ok',
            'casos' => $listarCasosComi
        ]);
        // en caso de no obtener los casos, se envia un json con el error
        } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $listarCasosComi ?? 'No hay casos para listar'
        ]); 
    }
    // se toma el catch para manejar errores  
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarCasosComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;

