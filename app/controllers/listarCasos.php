<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');


//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

try {

    $helper = new baseHelper($pdo); //instanciamos la clase baseHelper con la conexion a la base de datos
    $casosListados = $helper->consultObjectHelper("sp_listar_casos()"); //llamamos la funcion que necesitamos
    
    if ($casosListados) { //validamos el status y que la variable no este vacia
        echo json_encode([
            'status' => 'ok',
            'casos' => $casosListados
        ]); //retornamos el array asociativo con la data necesaria
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => $casosListados ?? 'No hay casos para mostrar'
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