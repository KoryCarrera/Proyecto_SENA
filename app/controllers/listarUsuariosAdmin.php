<?php

header('Content-Type: application/json');
// decimos que trabajaremos solo con json

// cargamos las dependencias necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

// se crea una instancia de baseHelper
$helper = new baseHelper($pdo);

try {
    // se llama al metodo consultObjectHelper
    $usuariosListados = $helper->consultObjectHelper('sp_listar_usuarios()');
    
    // se valida si se obtuvieron los usuarios, en caso de serlo, se envia un json 
    if ($usuariosListados) {
        echo json_encode([
            'status' => 'ok',
            'usuarios' => $usuariosListados,
        ]);

        // en caso de no obtener los usuarios, se envia un json con el error
    } else {
        echo json_encode ([
            'status' => 'error',
            'mensaje' => 'No hay usuarios por mostrar'
        ]);
    }
    // se toma el catch para manejar errores  
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarUsuarios.php ". $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => '!Error del servidor¡'
    ]);
}
exit;