<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Cargamos la session activa
session_start();

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/getData.php";
require_once __DIR__ .  "/checkSessionComi.php";


try {
	$listarCasosComi = listarCasosComi($pdo, $_SESSION['user']['documento']);
	
	if ($listarCasosComi && $listarCasosComi['status'] === 'ok') {
		echo json_encode([
            'status' => 'ok',
            'casos' => $listarCasosComi['data']
        ]);
        } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $listarCasosComi['mensaje'] ?? 'No hay casos para listar'
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

