<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";
require_once __DIR__ . "/../models/fileManager.php";
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../utils/utilsAuth.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {

    $modelCaso = new CasosModel($pdo);

    // Captura de datos
    $nombreCaso = $_POST["nombreCaso"] ?? null;
    $radicado = $_POST["radicadoSena"] ?? null;
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
    $registrar = $modelCaso->registrarCaso(
        $_SESSION['user']['documento'],
        $proceso,
        $tipoCaso,
        $descripcion,
        $nombreCaso,
        $radicado
    );

    // Verificar resultado
    if (!$registrar['success']) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el caso'
        ]);
        exit;
    }

    $idCaso = $registrar['data']['id_caso'];

    // Procesar archivos
    $resultadoArchivos = ['success' => false]; // Definirla por defecto

    if (isset($_FILES['archivos']) && !empty($_FILES['archivos']['name'][0])) {
        try {
            $fileManager = new FileManager($pdo);
            $guardado = $fileManager->guardarArchivosCaso($idCaso, $_FILES['archivos']);
            $resultadoArchivos = ['success' => true];
        } catch (Exception $e) {
            error_log("Error subiendo archivos: " . $e->getMessage());
            $resultadoArchivos = ['success' => false];
        }
    }

    $correo = correoRegistroCaso($idCaso, $nombreCaso, $proceso, $tipoCaso, $descripcion, $resultadoArchivos);

    if (!$correo) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Se registro el caso, pero no se pudo enviar el correo'
        ]);
        exit;
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
        'mensaje' => 'Error del servidor '
    ]);
}
