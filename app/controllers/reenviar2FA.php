<?php

use Dom\Document;

header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/baseHelper.php';
require_once __DIR__ . '/../utils/utilsAuth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
};

$helper = new baseHelper($pdo);

$solicitud = $_POST['solicitud'];


if ($solicitud) {
    $documento = $_SESSION['user']['documento'];

    if (!$documento) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => '¡Usuario no autentificado!'
        ]);
        exit;
    };

    $documentoData = [
        ['value' => $documento, 'type' => PDO::PARAM_STR]
    ];

    try {
        $helper->insertOrUpdateData('sp_eliminar_token_2fa(?)', $documentoData);

        $token = bin2hex(random_bytes(3));

        $dataToken = [
            ['value' => $documento, 'type' => PDO::PARAM_STR],
            ['value' => $token, 'type' => PDO::PARAM_STR],
        ];

        $helper->insertOrUpdateData('sp_guardar_token_2FA(?, ?)', $dataToken);

        enviarCodigo2FA($token, $_SESSION['user']['username'], $_SESSION['user']['email']);

        echo json_encode([
            'status' => 'ok'
        ]);

        exit;
    } catch (Exception $e) {
        error_log('Ha ocurrido un error a la hora de reenviar codigo: ' . $e->getMessage());
        throw new Exception('Ha ocurrido un error: ' . $e->getMessage());
    };
}
