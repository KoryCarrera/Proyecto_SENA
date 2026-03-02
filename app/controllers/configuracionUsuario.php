<?php
session_start();
require_once '../config/conexion.php'; 
require_once '../models/getData.php';
require_once '../models/updateData.php';

header('Content-Type: application/json');

// 1. Verificamos sesión
if (!isset($_SESSION['user']['documento'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada.']);
    exit;
}

$documento = $_SESSION['user']['documento'];
$confirmar_contraseña = $_POST['password_actual'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$numero = $_POST['numero'] ?? '';
$contraseña = $_POST['contrasena'] ?? '';

// 2. Validar identidad
$usuario = buscarUsuario($pdo, $documento);

if (!$usuario || !password_verify($confirmar_contraseña, $usuario['contraseña'])) {
    echo json_encode(['status' => 'error', 'message' => 'La contraseña actual no coincide.']);
    exit;
}

// 3. Llamar a la función del modelo con parámetros individuales
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
        'message' => 'Información actualizada correctamente.'
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error al procesar la actualización en la base de datos.'
    ]);
}