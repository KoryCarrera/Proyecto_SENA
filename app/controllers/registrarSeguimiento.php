<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {
    $documento = $_SESSION['user']['documento'];
    $observacion = $_POST["observacion"] ?? null;
    $idCaso = $_POST["caso"] ?? null;
    

     if (!$observacion || trim($observacion) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Llene la obervacion'
        ]);
        exit;
    }

    if (!$idCaso || trim($idCaso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El caso no existe'
        ]);
        exit;
    }
$resultado = registrarSeguimiento($pdo, $observacion, $idCaso, $documento);

    if ($resultado === true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Seguimiento registrado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el seguimiento'
        ]);
    }

} catch (Exception $e) {
    error_log("Error en registrarSeguimiento.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
