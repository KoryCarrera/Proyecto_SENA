<?php

//Se llaman las dependencias necesarias para el
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../utils/utilsEmail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
};

try {

    $correo = strtolower(str_replace(' ', '', $_POST['email'] ?? ''));
    $documento = strtolower(str_replace(' ', '', $_POST['documento'] ?? ''));
    $nombre = strtolower(str_replace(' ', '', $_POST['nombre'] ?? ''));
    $telefono = strtolower(str_replace(' ', '', $_POST['telefono'] ?? ''));

    $model = new UsuariosModdel($pdo);

    if (!$correo || !$documento || !$nombre || !$telefono) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Todos los campos son requeridos'
        ]);
        exit;
    };

    $validacion = $model->validarExistenciaUser($documento, $correo, $nombre, $telefono);

    if (!$validacion) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se ha podido validar el usuario'
        ]);
        exit;
    };

    $token = bin2hex(random_bytes(32));
    $model->tokenRecuperacion($documento, $token);

    $dataUser = [
        [ 'value' => $documento, 'type' => PDO::PARAM_STR ],
        [ 'value' => $token, 'type' => PDO::PARAM_STR ],
    ];

    $model->insertOrUpdateData('sp_guardar_token_2fa(?, ?)', $dataUser);

    $linkRecuperacion = 'http://localhost:8000/Recuperar_Password/' . $token;

    $correo = correoRecuperacionPassword($correo, $nombre, $linkRecuperacion);

    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Correo enviado con exito'
    ]);
    exit;
} catch (Exception) {

    error_log("Error en olvido password: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error interno: ' . $e->getMessage()
    ]);
};
