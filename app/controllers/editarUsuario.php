<?php

//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

session_start();

//incluimos las dependencias requeridas
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";
require_once __DIR__ . '/../utils/utilsEmail.php';

//validamos que el protocolo sea http

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no valido'
    ]);
    exit;
};

//recibimos todos las datos
$documento = $_POST['documento'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$numero = $_POST['numero'];
$rol = $_POST['rol'];
$contrasena = isset($_POST['generar_password']) && $_POST['generar_password'] === 'true';

try {
    $model = new UsuariosModdel($pdo);
    //validamos todos los datos
    if (
        !is_string($nombre) ||
        !is_string($apellido) ||
        !is_numeric($rol) ||
        !is_string($documento) ||
        !is_string($email) ||
        !is_string($numero)

    ) {
        // se envia un json con el error
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Tipos de datos no validos'
        ]);
        exit;
    }
    ;

    //llamamos el modelo que necesitamos
    $usuarioActualizado = $model->editarUsuario($documento, $nombre, $apellido, $email, $numero, $rol, $contrasena);

    //verificamos que la funcion haya devuelto true
    if (!$usuarioActualizado) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Se ha producido un error al actualizar el usuario'
        ]);
        exit;
    } else {

        // se llama a la funcion que envia el correo
    $correo = correoEdicionUsuario($usuarioActualizado, $nombre, $email, $rol);

        // se envia un json con el mensaje exitoso
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Usuario actualizado con exito'
        ]);
    }
// el correo esta enviando unerror si no se mando 
    if (!$correo) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'no se mando el correo'
        ]);
        exit;
    }

    exit;
} 
// se toma el catch para manejar errores  
catch (Exception $e) {
    error_log('Error al editar usuario: ' . $e->getMessage());
    throw new Exception($e);
}
