<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Llamamos la credenciales necesarias
require_once "../config/conexion.php";
require_once "../models/getData.php";

try {
    $casosListados = listarCasos($pdo); //llamamos la funcion que necesitamos
    
    if ($casosListados && $casosListados['status'] === 'ok') { //validamos el status y que la variable no este vacia
        echo json_encode([
            'status' => 'ok',
            'casos' => $casosListados['data']
        ]); //retornamos el array asociativo con la data necesaria
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $casosListados['mensaje'] ?? 'No hay casos para mostrar'
        ]); //en caso tal de false o null retornamos "Sin casos para mostrar"
    }
    
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarCasos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;