<?php
// se especifica que la respuesta sera en formato JSON
header('Content-Type: application/json; charset=utf-8');

// se inicia la sesion
session_start();

// se incluyen los archivos necesarios
require_once __DIR__ . "/checkSessionAdmin.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";
require_once __DIR__ . "/../utils/utilsEmail.php"; // SOLUCIÓN: Faltaba incluir el archivo de correos

// se valida que el metodo sea POST
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
        echo json_encode(['status' => 'error', 'mensaje' => 'El nombre es requerido']);
        exit;
    }

    if (!$descripcion || !is_string($descripcion) || trim($descripcion) === '') {
        echo json_encode(['status' => 'error', 'mensaje' => 'La descripción es requerida']);
        exit;
    }

    // se registra el proceso
    $resultado = $model->registrarProceso($descripcion, $nombre, $documentoUsuario);

    // Se obtiene el correo del administrador para enviar la notificación
    $correoAdmin = $model->consultSimpleHelper("sp_obtener_correo_administrador()");

    // SOLUCIÓN: Agrupamos el éxito, el correo y el exit en un solo bloque
    if ($resultado['success'] == true) {
        
        // SOLUCIÓN: Cambiamos $registrar por $resultado
        $idProceso = $resultado['data']['id_proceso'] ?? null;

        // Intentamos enviar el correo
        $correoEnviado = false;
        if ($idProceso) {
            $correoEnviado = correoRegistrarProceso($idProceso, $nombre, $descripcion, $correoAdmin);
        }

        if (!$correoEnviado) {
            echo json_encode([
                'status' => 'ok',
                'mensaje' => 'Proceso registrado exitosamente, pero no se pudo enviar el correo de notificación.'
            ]);
        } else {
            echo json_encode([
                'status' => 'ok',
                'mensaje' => 'Proceso registrado exitosamente y correo enviado.'
            ]);
        }
        exit; // Detenemos la ejecución aquí
        
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el proceso'
        ]);
        exit;
    }

} catch (Exception $e) {
    error_log("Error en registrarProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
    exit;
}