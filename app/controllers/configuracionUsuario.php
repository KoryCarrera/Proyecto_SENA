<?php
header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../config/conexion.php'; 
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";

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
$contraseña = $_POST['contrasena'] ?? '';
$numero = $_POST['numero'] ?? '';

try {
    $model = new UsuariosModdel($pdo);

    // Declaramos variable
    $resultado = $model->configuracionPerfilUsuario($documento, $confirmar_contraseña, $nombre, $apellido, $email, $contraseña, $numero);

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
} catch (Exception $e) {
  error_log('Ha ocurrido un error a la hora de configurar el perfil del usuario: ' . $e->getMessage());

  throw new Exception($e->getMessage());
} 

