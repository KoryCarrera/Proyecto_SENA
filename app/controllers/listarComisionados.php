<?php

//Definimos el header
header('Content-Type: application/json');

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";

//Inicializamos la session 
session_start();

//Inicializamos la clase
$model = new CasosModel($pdo);
try {

    //Hacemos una consulta y asignamos el resultado a una variable
    $listarComisionados = $model->consultObjectHelper('sp_listar_comisionados_y_casos');

    //Validamos el retorno y en caso de falo retornamos un error personalizado
    if (!$listarComisionados || !is_array($listarComisionados)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se han podido recuperar los comisionados'
        ]);
        exit;
    }

    //Si pasa la validacion anterior enviamos el resultado
    echo json_encode([
        'status' => 'ok',
        'data' => $listarComisionados
    ]);

} catch (Exception) {

    //Manejo de errores genericos
    error_log("Error en listarCasosComi.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);

}
exit;
