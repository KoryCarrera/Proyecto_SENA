<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";
require_once __DIR__ . "/../models/fileManager.php";
require_once __DIR__ . '/../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {
    // Captura de datos
    $nombreCaso = $_POST["nombreCaso"] ?? null;
    $proceso = $_POST["proceso"] ?? null;
    $tipoCaso = $_POST["tipoCaso"] ?? null;
    $descripcion = $_POST["descripcion"] ?? null;

    // Validación del nombre
    if (!$nombreCaso || trim($nombreCaso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El nombre del caso es requerido'
        ]);
        exit;
    }

    // Validación del proceso
    if (!$proceso || trim($proceso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El proceso es requerido'
        ]);
        exit;
    }

    // Validación del tipo de caso
    if (!$tipoCaso || trim($tipoCaso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El tipo es requerido'
        ]);
        exit;
    }

    // Validación de la descripción
    if (!$descripcion || trim($descripcion) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'La descripción es requerida'
        ]);
        exit;
    }

    // Registrar el caso
    $registrar = registrarCasos(
        $pdo,
        $_SESSION['user']['documento'],
        $proceso,
        $tipoCaso,
        $descripcion,
        $nombreCaso
    );

    // Verificar resultado
    if (!$registrar['success']) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el caso'
        ]);
        exit;
    }

    $idCaso = $registrar['id_caso'];

    // Procesar archivos (si existen)
    if (isset($_FILES['archivos']) && !empty($_FILES['archivos']['name'][0])) {
        
        $resultadoArchivos = procesarArchivos($pdo, $idCaso, $_FILES['archivos']);
        
        if (!$resultadoArchivos['success']) {
            error_log("Error al subir archivos para caso #{$idCaso}: " . $resultadoArchivos['mensaje']);
        }
    }

    // Respuesta exitosa
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Caso registrado exitosamente'
        ]);

} catch (Exception $e) {
    error_log("Error en registrarCasos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}