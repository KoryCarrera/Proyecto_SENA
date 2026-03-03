<?php
header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../config/conexion.php'; 
require_once __DIR__ . '/../models/getData.php';
require_once __DIR__ . '/../models/updateData.php';

// Verificamos sesión
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}
$documento = $_SESSION['user']['documento'];
$confirmar_contraseña = $_POST['password_actual'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$numero = $_POST['numero'] ?? '';
$contraseña = $_POST['contrasena'] ?? '';

// Validar identidad
$usuario = buscarUsuario($pdo, $documento);

if (!$usuario || !password_verify($confirmar_contraseña, $usuario['contraseña'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'La contraseña actual no coincide.']);
    exit;
}
$resultado = ConfigurarInfoUsuario(
    $pdo,
    $documento,
    $nombre,
    $apellido,
    $email,
    $contraseña,
    $numero
);

if ($resultado) {
    // Actualizar sesión para reflejar cambios 
    if (!empty($_POST['nombre'])) $_SESSION['user']['nombre'] = $_POST['nombre'];
    if (!empty($_POST['apellido'])) $_SESSION['user']['apellido'] = $_POST['apellido'];
    if (!empty($_POST['email'])) $_SESSION['user']['email'] = $_POST['email'];

    echo json_encode([
        'status' => 'ok', 
        'mensaje' => 'Información actualizada correctamente.'
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'mensaje' => 'Error al procesar la actualización en la base de datos.'
    ]);
}