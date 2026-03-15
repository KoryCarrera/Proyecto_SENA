<?php

header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/baseHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
};

$helper = new UsuariosModdel($pdo);

$redirect = ($_SESSION['user']['id_rol'] == 1) ? '/dashboardAdmin' : '/dashboardComi';

$codigo = strip_tags(htmlspecialchars(trim($_POST['codigo'])));
$documento = $_SESSION['user']['documento'];

if (!$codigo) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡El codigo es obligatorio!'
    ]);
    exit;
};

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

$findToken = $helper->consultSimpleWithParams('sp_consultar_token_2FA(?)', $documentoData);

if ($findToken == $codigo) {

    $dataUser = [
        [ 'value' => $_SESSION['user']['documento'], 'type' => PDO::PARAM_STR]
    ];

    $_SESSION['user']['verify'] = true;

    $helper->generarCookie($_SESSION['user']['documento'], $_SESSION['user']['verify']);

    $helper->insertOrUpdateData('sp_eliminar_token_2fa(?)', $dataUser);
    
    return [
        'status' => 'ok',
        'redirect' => $redirect
    ];
};

echo json_encode([
    'status' => 'error',
    'mensaje' => '¡Codigo de 2FA incorrecto!'
]);
