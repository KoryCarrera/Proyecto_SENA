<?php

// se especifica que la respuesta sera en formato JSON
header('Content-Type: application/json');

// se inicia la sesion
session_start();

// se incluyen los archivos necesarios
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

    // se crea una instancia del modelo de casos
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

    // se obtiene el id del caso
    $idCaso = $registrar['data']['id_caso'];

    // Procesar archivos
    $resultadoArchivos = ['success' => false];

    // se valida si se han enviado archivos
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

    // se envia el correo de registro
    $correo = correoRegistroCaso($idCaso, $nombreCaso, $registrar['data']['proceso'], $registrar['data']['tipo_caso'], $descripcion, $resultadoArchivos);

    // se valida si se pudo enviar el correo
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
