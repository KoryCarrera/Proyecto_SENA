<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
};

try {
    $token = $_POST['token'];
    $nuevaPass = $_POST['nuevaContrasena'];
    $confirmar = $_POST['confirmacionContrasena'];

    if (!$token || !$nuevaPass || !$confirmar) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Todos los campos son requeridos',
        ]);
        exit;
    };

    if ($nuevaPass !== $confirmar) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Las contraseñas no coinciden'
        ]);
        exit;
    };

    $model = new UsuariosModdel($pdo);

    $dataToken = [
        ['value' => $token, 'type' => PDO::PARAM_STR]
    ];

    $user = $model->consultSimpleWithParams('sp_usuario_por_token(?)', $dataToken);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se puede autentificar al usuario'
        ]);
        exit;
    };

    $model->actualizarContrasena($user['documento'], $nuevaPass);

    $dataUser = [
        ['value' => $user['documento'], 'type' => PDO::PARAM_STR]
    ];

    $model->insertOrUpdateData('sp_eliminar_token_2fa(?)', $dataUser);

    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Se ha actualizado la contraseña con exito'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Error al actualizar contraseña ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al actualizar contraseña ' . $e->getMessage()
    ]);
    exit;
}
