<?php

// Indicamos que la respuesta del controlador será en formato JSON
header('Content-Type: application/json');

// Inclusión de dependencias
require_once __DIR__ . "/checkSessionComi.php";

// Conexión a la base de datos (PDO)
require_once __DIR__ . "/../config/conexion.php";

// Modelo que contiene la función para insertar datos
require_once __DIR__ . "/../models/insertData.php";

// Autoload de Composer (por si se usan librerías externas)
require_once __DIR__ . '/../../vendor/autoload.php';


// Este controlador solo acepta peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status'  => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit; // Detiene la ejecución
}

try {
	
    // Captura de datos enviados por POST

    $proceso     = $_POST["proceso"] ?? null;
    $estado      = $_POST["estado"] ?? null;
    $tipoCaso    = $_POST["tipoCaso"] ?? null;
    $descripcion = $_POST["descripcion"] ?? null;


    // Validación del proceso
    if (!$proceso || !is_string($proceso) || trim($proceso) === '') {
        echo json_encode([
            'status'  => 'error',
            'mensaje' => 'El proceso es requerido'
        ]);
        exit;
    }

    // Validación del estado
    if (!$estado || !is_string($estado) || trim($estado) === '') {
        echo json_encode([
            'status'  => 'error',
            'mensaje' => 'El estado es requerido'
        ]);
        exit;
    }

    // Validación del tipo de caso
    if (!$tipoCaso || !is_string($tipoCaso) || trim($tipoCaso) === '') {
        echo json_encode([
            'status'  => 'error',
            'mensaje' => 'El tipo es requerido'
        ]);
        exit;
    }

    // Validación de la descripción
    if (!$descripcion || !is_string($descripcion) || trim($descripcion) === '') {
        echo json_encode([
            'status'  => 'error',
            'mensaje' => 'La descripción es requerida'
        ]);
        exit;
    }

    // Se envían los datos al modelo para registrar el caso
    $registrar = registrarCasos(
        $pdo,
        $_SESSION['user']['documento'], // Documento del usuario logueado
        $proceso,
        $estado,
        $tipoCaso,
        $descripcion
    );

    // Respuesta según resultado

    if ($registrar === true) {
        echo json_encode([
            'status'  => 'ok',
            'mensaje' => 'Caso registrado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'mensaje' => 'Error al registrar el caso'
        ]);
    }

} catch (Exception $e) {


    // Se registra el error en los logs del servidor
    error_log("Error en registrarCasos.php: " . $e->getMessage());

    // Respuesta genérica al cliente
    echo json_encode([
        'status'  => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}

