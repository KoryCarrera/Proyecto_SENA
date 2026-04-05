<?php
//Indicamos que la respuesta sera JSON
header('Content-Type: application/json');

//Inicializamos session
session_start();

//Inclusión de dependencias
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/baseHelper.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/seguridad.php';
require_once __DIR__ . '/../utils/utilsEmail.php';

$helper = new baseHelper($pdo);
$model = new UsuariosModdel($pdo);

//Solo permitimos solicitudes tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
}

//Captura de credenciales
$documentoInseguro = $_POST['documento'] ?? '';
$contrasena = $_POST['password'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

//Validamos el token
if (!validarCsrfToken($csrf_token)) {
    session_destroy();
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Error de seguridad recargue la pagina!'
    ]);
    exit;
};

//Validamos que no se hayan recibido valores vacios
if (empty($documentoInseguro) || empty($contrasena)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Valores vacios!'
    ]);
    exit;
};

//Limpiamos el documento del front
$documento = limpiar($documentoInseguro);

try {
    //Verificamos con el modelo
    $verificacion = $model->loginUsuario($documento, $contrasena);

    //Configuracion de variables de session
    $_SESSION['user'] = [
        'documento' => $verificacion['documento'],
        'username' => $verificacion['username'],
        'id_rol' => $verificacion['id_rol'],
        'email' => $verificacion['email']
    ];

    //Marca de tiempo de inactividad
    $_SESSION['ultima_actividad'] = time();

    unset($_SESSION['csrf_token']);

    if ($verificacion['id_rol'] == 1) {

        //Regeneramos la session
        session_regenerate_id(true);

        // si el usuario no tiene 2FA, se envia un json con el estado ok y la redireccion

        if (!$verificacion['2FA']) {
            $_SESSION['user']['verify'] = true;
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardAdmin'
            ]);
            exit;
        };

        // si el usuario tiene 2FA, se valida el dispositivo

        $validacion = $model->validarDispositivo($documento);

        // si el dispositivo es valido, se envia un json con el estado ok y la redireccion

        if ($validacion) {
            $_SESSION['user']['verify'] = true;
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardAdmin'
            ]);
            exit;
        };

        // si el dispositivo no es valido, se genera un token y se envia un json con el estado ok y la redireccion

        $token = bin2hex(random_bytes(3));
        
        // se crea un array con el documento y el token
        $dataToken = [
            ['value' => $documento, 'type' => PDO::PARAM_STR],
            ['value' => $token, 'type' => PDO::PARAM_STR],
        ];

        // se llama al metodo insertOrUpdateData
        $helper->insertOrUpdateData('sp_guardar_token_2fa(?, ?)', $dataToken);

        // se envia el codigo 2FA
        enviarCodigo2FA($token, $verificacion['username'], $verificacion['email']);

        // se envia un json con el estado ok y la redireccion
        echo json_encode([
            'status' => 'ok',
            'redirect' => '/2FA'
        ]);
        exit;
    };

    // si el usuario es comisionado, se envia un json con el estado ok y la redireccion
    if ($verificacion['id_rol'] == 2) {

        //Regeneramos la session
        session_regenerate_id(true);

        //si el usuario no tiene 2FA, se envia un json con el estado ok y la redireccion
        if (!$verificacion['2FA']) {
            $_SESSION['user']['verify'] = true;
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardComi'
            ]);
            exit;
        };

        // si el usuario tiene 2FA, se valida el dispositivo
        $validacion = $model->validarDispositivo($documento);

        // si el dispositivo es valido, se envia un json con el estado ok y la redireccion
        if ($validacion) {
            $_SESSION['user']['verify'] = true;
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardComi'
            ]);
            exit;
        };

        // si el dispositivo no es valido, se genera un token
        $token = bin2hex(random_bytes(3));

        // se crea un array con el documento y el token
        $dataToken = [
            ['value' => $documento, 'type' => PDO::PARAM_STR],
            ['value' => $token, 'type' => PDO::PARAM_STR],
        ];

        // se llama al metodo insertOrUpdateData
        $helper->insertOrUpdateData('sp_guardar_token_2FA(?, ?)', $dataToken);
        // se envia el codigo 2FA
        enviarCodigo2FA($token, $verificacion['username'], $verificacion['email']);

        // se envia un json con el estado ok y la redireccion
        echo json_encode([
            'status' => 'ok',
            'redirect' => '/2FA'
        ]);
        exit;
    }

    // si el rol es desconocido, se envia un json con el estado error y el mensaje rol desconocido
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Rol desconocido'
    ]);
    exit;
    // se toma el catch para manejar errores
} catch (Exception $e) {
    // se registra el error
    error_log('¡Ha ocurrido un error al loguear: ' . $e->getMessage());

    // se envia un json con el estado error y el mensaje de error
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
    exit;
}
