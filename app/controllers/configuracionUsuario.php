<?php

//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

session_start();

//incluimos las dependencias requeridas
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/updateData.php";

//validamos que el protocolo sea http

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no valido'
    ]);
    exit;
};

//recibimos todos las datos
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$contraseñaNueva ['contrasena'];
$contrasenaActual = $_POST['password_actual'];
$numero = $_POST['numero'];
$documento = $_SESSION['user']['documento'];

//validamos todos los datos
if (
    !is_string($nombre) ||
    !is_string($apellido) ||
    !is_string($contraseñaNueva) ||
    !is_string($contrasenaVieja) ||
    !is_string($email) ||
    !is_string($numero) ||
    !is_string($documento)
) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Tipos de datos no validos'
    ]);
    exit;
};



//llamamos el modelo que necesitamos
$usuarioActualizado = ConfigurarInfoUsuario($pdo, $documento, $nombre, $apellido, $email, $contrasena, $numero);

//verificamos que la funcion haya devuelto true
if (!$usuarioActualizado) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Se ha producido un error al actualizar el usuario'
    ]);
    exit;
} else {
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Usuario actualizado con exito'
    ]);
}

exit;