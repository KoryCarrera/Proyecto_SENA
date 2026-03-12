<?php
//Indicamos que la respuesta sera JSON
header('Content-Type: aplication/json');

//Inclusión de dependencias
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/baseHelper.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/seguridad.php';

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
if (!$documentoInseguro && !$contrasena) {
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

    //Regeneramos la session
    session_regenerate_id(true);

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

        if (!$verificacion['2FA']) {
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardAdmin'
            ]);
            exit;
        };

        $validacion = $model->validarDispositivo($documento);

        if ($validacion) {
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardAdmin'
            ]);
            exit;
        };

        echo json_encode([
            'status' => 'ok',
            'redirect' => '/2FA'
        ]);
        exit;
    };

    if ($verificacion['id_rol'] == 2) {

        if (!$verificacion['2FA']) {
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardComi'
            ]);
            exit;
        };

        $validacion = $model->validarDispositivo($documento);

        if ($validacion) {
            echo json_encode([
                'status' => 'ok',
                'redirect' => '/dashboardComi'
            ]);
            exit;
        };

        echo json_encode([
            'status' => 'ok',
            'redirect' => '/2FA'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Rol desconocido'
    ]);
    exit;
} catch (Exception $e) {

    error_log('¡Ha ocurrido un error al loguear: ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
    exit;
}
