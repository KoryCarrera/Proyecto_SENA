<?php
header('Content-Type: application/json');

require_once __DIR__ . "/checkSessionAdmin.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {

    $model = new CasosModel($pdo);

    $documentoUsuario = $_SESSION['user']['documento'];
    $nombre = $_POST["nombre-proceso"] ?? null;
    $descripcion = $_POST["descripcion"] ?? null;

    if (!$nombre || !is_string($nombre) || trim($nombre) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El nombre es requerido'
        ]);
        exit;
    }

    if (!$descripcion || !is_string($descripcion) || trim($descripcion) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'La descripción es requerida'
        ]);
        exit;
    }

    $resultado = $model->registrarProceso($descripcion, $nombre, $documentoUsuario);

    if ($resultado['success'] == true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso registrado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el proceso'
        ]);
    }

    $idProceso = $registrar['data']['id_proceso'];

    $correo = correoRegistrarProceso($idProceso, $nombre, $descripcion);

    if (!$correo) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'no se mando el correo'
        ]);
        exit;
    }

} catch (Exception $e) {
    error_log("Error en registrarProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
