<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Cargamos la session activa
session_start();

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

// se crea una instancia de baseHelper
$helper = new baseHelper($pdo);

try {
    // se toma el id del caso
    $idCaso = $_POST['idcaso'];

    // se crea un array con el id del caso
    $data = [
        ['value' => $idCaso, 'type' => PDO::PARAM_INT]
    ];
    // se llama al metodo consultObjectWithParams
    $listarSeguimientos = $helper->consultObjectWithParams('sp_listar_seguimientos_por_caso(?)', $data);

    // se valida si se obtuvieron los seguimientos, en caso de serlo, se envia un json 
    if ($listarSeguimientos) {
        echo json_encode([
            'status' => 'ok',
            'seguimientos' => $listarSeguimientos
        ]);

        // en caso de no obtener los seguimientos, se envia un json con el error
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No hay seguimientos para listar'
        ]);
    }
    // se toma el catch para manejar errores  sql
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarSeguimientos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;
