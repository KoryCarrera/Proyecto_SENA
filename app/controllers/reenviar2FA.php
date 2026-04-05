<?php

use Dom\Document;

header('Content-Type: application/json');
// se indica que solo se trabajara con json

// se inicia la sesion
session_start();

// se incluyen las dependencias necesarias
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/baseHelper.php';
require_once __DIR__ . '/../utils/utilsAuth.php';

// se valida que el metodo sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
};

// se crea una instancia del modelo de casos
$helper = new baseHelper($pdo);

// se obtiene el documento del usuario
$solicitud = $_POST['solicitud'];

// se valida si la solicitud es valida
if ($solicitud) {
    $documento = $_SESSION['user']['documento'];

    // se valida si el documento es valido
    if (!$documento) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => '¡Usuario no autentificado!'
        ]);
        exit;
    };

    // se crea un array con el documento del usuario
    $documentoData = [
        ['value' => $documento, 'type' => PDO::PARAM_STR]
    ];

    try {
        // se elimina el token anterior
        $helper->insertOrUpdateData('sp_eliminar_token_2fa(?)', $documentoData);

        // se genera un nuevo token
        $token = bin2hex(random_bytes(3));

        // se crea un array con el documento y el token
        $dataToken = [
            ['value' => $documento, 'type' => PDO::PARAM_STR],
            ['value' => $token, 'type' => PDO::PARAM_STR],
        ];

        // se guarda el token
        $helper->insertOrUpdateData('sp_guardar_token_2FA(?, ?)', $dataToken);

        // se envia el correo con el codigo 2FA
        enviarCodigo2FA($token, $_SESSION['user']['username'], $_SESSION['user']['email']);

        // se envia un json con el estado ok
        echo json_encode([
            'status' => 'ok'
        ]);

        exit;
    } catch (Exception $e) {
        error_log('Ha ocurrido un error a la hora de reenviar codigo: ' . $e->getMessage());
        throw new Exception('Ha ocurrido un error: ' . $e->getMessage());
    };
}
