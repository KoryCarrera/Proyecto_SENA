<?php
// Definimos el tipo de archivo que llegará y enviará
header('Content-Type: application/json');

session_start();

// Inclusión de dependencias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";
require_once __DIR__ . "/../utils/utilsEmail.php";

// Validamos protocolo http
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

// Se capturan los datos de la sesión de forma segura
$documentoSession = $_SESSION['user']['documento'] ?? null;
$emailDestino = $_SESSION['user']['email'] ?? null;   
$nombreUsuario = $_SESSION['user']['nombre'] ?? 'Usuario'; 

// Se capturan los datos del POST
$documento = $_POST['documento'] ?? null;
$estado = $_POST['estado'] ?? null;
$motivo = $_POST['motivo'] ?? null;

// Se valida que los datos no sean nulos (usamos isset para el estado por si envían 0)
// Añadimos la validación del correo para asegurarnos de que la sesión lo tenga
if (!$documento || !isset($estado) || !$motivo || !$emailDestino) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Valores vacíos, incompletos o sesión inválida'
    ]);
    exit;
}

// Usamos try catch para manejar posibles errores
try {
    $model = new UsuariosModdel($pdo);

    //  Se cambia el estado del usuario en la base de datos
    $model->cambiarEstadoUsuario($documento, $estado, $documentoSession, $motivo);

    //  Se envía la notificación por correo usando los datos de la sesión
    $correoEnviado = correoCambioEstado($emailDestino, $nombreUsuario, $estado, $motivo);

    //  Respuesta final unificada al frontend
    if ($correoEnviado) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Estado actualizado y correo de notificación enviado con éxito'
        ]);
    } else {
        echo json_encode([
            'status' => 'warning',
            'mensaje' => 'Estado actualizado, pero hubo un problema al enviar el correo'
        ]);
    }

// Manejo de errores
} catch (Exception $e) {
    error_log('Ha ocurrido un error a la hora de cambiar estado usuario: ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Ocurrió un error interno en el servidor.'
    ]);
    exit;
}