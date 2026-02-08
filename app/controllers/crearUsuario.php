<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";

//matamos el script con exit para matar el codigo en cada validacion incorrecta
//validamos que el metodo sea post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}

//capturamos los datos del usuario
$documento = $_POST['documento'];
$rol = $_POST['rol'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

//validacion de datos
if(!$documento || !$rol || !$nombre || !$apellido || !$email || !$contrasena) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Todos los datos son requeridos'
    ]);
    exit;
};

if (!is_numeric($rol)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Valor de rol no valido'
    ]);
    exit;
};

if(!is_string($nombre) || !is_string($apellido) || !is_string($email) || !is_string($contrasena)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Datos no validos'
    ]);
    exit;
}

//si todo está verdadero insertamos el usuario
$usuarioRegistrado = registrarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $contrasena);

if(!$usuarioRegistrado) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al registrar al usuario'
    ]);
    exit;
} else {
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Usuario registrado con exito'
    ]);
};
exit;