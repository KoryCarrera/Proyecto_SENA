<?php

//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

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
$rol = $_POST['rol'];
$documento = $_POST['documento'];
$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

//validamos todos los datos
if (
    !is_string($nombre) ||
    !is_string($apellido) ||
    !is_numeric($rol) ||
    !is_string($documento) ||
    !is_string($email)
) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Tipos de datos no validos'
    ]);
    exit;
};

//llamamos el modelo que necesitamos
$usuarioActualizado = ActualizarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $contrasena);

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