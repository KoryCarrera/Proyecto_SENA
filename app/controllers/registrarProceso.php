<?php
// se especifica que la respuesta sera en formato JSON
header('Content-Type: application/json');

// se inicia la sesion
session_start();

// se incluyen los archivos necesarios
require_once __DIR__ . "/checkSessionAdmin.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";

// se valida que el metodo sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

// inicia el try para manejar errores
try {

    // se crea una instancia del modelo de casos
    $model = new CasosModel($pdo);

    // se obtiene el documento del usuario
    $documentoUsuario = $_SESSION['user']['documento'];
    $nombre = $_POST["nombre-proceso"] ?? null;
    $descripcion = $_POST["descripcion"] ?? null;

    // se valida que el nombre sea valido
    if (!$nombre || !is_string($nombre) || trim($nombre) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El nombre es requerido'
        ]);
        exit;
    }

    // se valida que la descripcion sea valida
    if (!$descripcion || !is_string($descripcion) || trim($descripcion) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'La descripción es requerida'
        ]);
        exit;
    }

    // se registra el proceso
    $resultado = $model->registrarProceso($descripcion, $nombre, $documentoUsuario);

    // se valida si el proceso se registro exitosamente
    if ($resultado['success'] == true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso registrado exitosamente'
        ]);

    } // si no se registro exitosamente, muestra el error
     else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el proceso'
        ]);
    }

    // se obtiene el id del proceso
    $idProceso = $registrar['data']['id_proceso'];

    // se envia el correo de registro
    $correo = correoRegistrarProceso($idProceso, $nombre, $descripcion);

    // se valida si se pudo enviar el correo
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
