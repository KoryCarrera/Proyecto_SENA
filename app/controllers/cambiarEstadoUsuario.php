<?php
header('Content-Type: application/json');

session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";
require_once __DIR__ . "/../utils/utilsEmail.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
    exit;
}

// Datos de quien realiza la acción (Sesión - El Admin)
$documentoSession = $_SESSION['user']['documento'] ?? null;

// Datos del POST (El usuario al que se le cambia el estado)
$documento = $_POST['documento'] ?? null;
$estado = $_POST['estado'] ?? null;
$motivo = $_POST['motivo'] ?? null;

if (!$documento || !isset($estado) || !$motivo) {
    echo json_encode([
        'status' => 'error', 
        'mensaje' => 'Valores vacíos o incompletos'
    ]);
    exit;
}

try {
    $model = new UsuariosModdel($pdo);

    // 1. Preparamos el parámetro para buscar al usuario afectado
    $parametroBusqueda = [
        ['value' => $documento, 'type' => PDO::PARAM_STR]
    ];

    // 2. Buscamos los datos usando tu SP "traer_usuario"
    // Pasamos el nombre del SP y el array de parámetros
    $usuarioAfectado = $model->consultSimpleWithParams('sp_traer_usuario(?)', $parametroBusqueda);

    if (!$usuarioAfectado) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No se encontró el usuario para notificar'
        ]);
        exit;
    }

    // Extraemos email y nombre de lo que devolvió tu SP
    $emailDestino = $usuarioAfectado['email'];
    $nombreCompleto = $usuarioAfectado['nombre'] . ' ' . ($usuarioAfectado['apellido'] ?? '');

    // 3. Ejecutamos el cambio de estado en la base de datos
    $model->cambiarEstadoUsuario($documento, $estado, $documentoSession, $motivo);

    // 4. Enviamos el correo al usuario (usando los datos frescos de la BD)
    $correoEnviado = correoCambioEstado($emailDestino, $nombreCompleto, $estado, $motivo);

    if ($correoEnviado) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Estado actualizado y notificación enviada con éxito'
        ]);
    } else {
        echo json_encode([
            'status' => 'warning',
            'mensaje' => 'Estado actualizado, pero hubo un error al enviar el correo'
        ]);
    }

} catch (Exception $e) {
    error_log('Error en cambio de estado: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error interno: ' . $e->getMessage()
    ]);
    exit;
}