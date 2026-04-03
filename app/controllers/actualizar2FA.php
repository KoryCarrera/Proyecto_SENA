<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/usuariosModel.php";

if ($_SERVER['REQUEST_METHOD'] !==  'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}

try {
    $model = new UsuariosModdel($pdo);

    $documento = $_SESSION['user']['documento'];
    $estado = isset($_POST['estado_2fa']) ? (int)$_POST['estado_2fa'] : null;

    if (!$documento || $estado === null) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Alguno de los datos requeridos no ha sido capturado'
        ]);
        exit;
    }

    if ($estado !== 0 && $estado !== 1) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Valor de estado inválido'
        ]);
        exit;
    }

    $activar = $model->activar2FA($documento, $estado);

    if (!$activar) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se ha podido ejecutar la accion deseada'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'ok',
        'mensaje' => '2FA actualizado correctamente'
    ]);

} catch (Exception $e) {
    error_log('Ha ocurrido un error a la hora de intenar ejecutar la accion en activarFA.php: ' . $e->getMessage());
    //se crea una excepcion
   echo json_encode([
    'status' => 'error',
    'mensaje' => 'Ocurrió un error en el servidor' . $e->getMessage()
   ]);
}
