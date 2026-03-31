<?php

header('Content-Type: application/json');

//se define el formato de respuesta y peticion de con la que trabajamos (json)

session_start();

//se inicia la sesion para poder acceder a los datos del usuario

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/baseHelper.php';

//llamamos la dependencias necesarias para el funcionamiento del controlador

//se valida que el metodo sea POST y si no simplemenete finaliza el codigo 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
}
;
//usamos try catch para manejar posibles errores de conexion a la base de datos
try {
    $helper = new UsuariosModdel($pdo);

    //se define la ruta de redireccion dependiendo del rol del usuario,si es igual a 1 es admin y 
    //si es distinto,entonces es comisionado

    $redirect = ($_SESSION['user']['id_rol'] == 1) ? '/dashboardAdmin' : '/dashboardComi';

    //se captura el codigo y el documento del usuario junto con el mismo usuario

    $codigo = $_POST['codigo'];
    $documento = $_SESSION['user']['documento'];

    //se valida que el codigo no sea nulo,de serlo,se envia un mensaje de error

    if (!$codigo) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => '¡El codigo es obligatorio!'
        ]);
        exit;
    }
    ;

    //se valida que el documento no sea nulo,de serlo,se envia un mensaje de error

    if (!$documento) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => '¡Usuario no autentificado!'
        ]);
        exit;
    }
    ;

    //se crea un array con los datos del usuario para pasarlos como parametro a la funcion

    $documentoData = [
        ['value' => $documento, 'type' => PDO::PARAM_STR]
    ];

    //se consulta el token 2fa del usuario

    $findToken = $helper->consultSimpleWithParams('sp_consultar_token_2fa(?)', $documentoData);

    //se valida que el token sea correcto

    if ($findToken['token'] == $codigo) {

        //se crea un array con los datos del usuario para pasarlos como parametro a la funcion

        $dataUser = [
            ['value' => $documento, 'type' => PDO::PARAM_STR]
        ];

        //se actualiza la variable de sesion verify

        $_SESSION['user']['verify'] = true;

        //se genera la cookie para el usuario

        $helper->generarCookie($documento, $_SESSION['user']['verify']);

        //se elimina el token 2fa del usuario

        $helper->insertOrUpdateData('sp_eliminar_token_2fa(?)', $dataUser);

        //se envia un mensaje de exito y la ruta de redireccion por medio de un json  y finaliza el codigo

        echo json_encode([
            'status' => 'ok',
            'redirect' => $redirect
        ]);
        exit;
    }
    ;

    //se envia un mensaje de error y se finaliza el codigo

    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Codigo de 2FA incorrecto!'
    ]);
    exit;

    //manejo de errores
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Error de conexión a la base de datos!'
    ]);
    exit;
}