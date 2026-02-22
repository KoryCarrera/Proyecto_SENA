<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Cargamos la session activa
session_start();

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";
require_once __DIR__ . "/checkSessionComi.php";


try {
    $idCaso = $_POST['id_caso'];

	$listarSeguimientos = obtenerSeguimientosPorCaso($pdo, $idCaso);
	
	if ($listarSeguimientos && $listarSeguimientos['status'] === 'ok') {
		echo json_encode([
            'status' => 'ok',
            'seg' => $listarSeguimientos['data']
        ]);
        } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $listarSeguimientos['mensaje'] ?? 'No hay seguimientos para listar'
        ]); 
    }
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarSeguimientos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;