<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

session_start();

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";
require_once __DIR__ . "/../utils/utilsAuth.php";

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
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$numero = $_POST['telefono'];
$rol = $_POST['rol'];

//usamos try catch para manejar posibles errores de conexion a la base de datos

try {
    $model = new UsuariosModdel($pdo);

    
    //validacion de datos
if(!$documento || !$nombre || !$apellido || !$email || !$numero || !$rol ) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Todos los datos son requeridos'
    ]);
    exit;
};

//se valida que el rol sea un numero

if (!is_numeric($rol)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Valor de rol no valido'
    ]);
    exit;
} 

//manejo de errores

} catch (Exception $e) {
    error_log('Error al crear usuario: ' . $e->getMessage());
    throw new Exception($e);
}

//se valida que los datos sean strings

if(!is_string($nombre) || !is_string($apellido) || !is_string($email) || !is_string($numero)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Datos no validos'
    ]);
    exit;
}
//si todo está verdadero insertamos el usuario
$usuarioRegistrado = $model->crearUsuario( $documento,  $nombre, $apellido, $email, $numero, $rol);

//se valida que el usuario se haya registrado correctamente, si no, se detiene el codigo y escribe un error 

if(!$usuarioRegistrado) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al registrar al usuario'
    ]);
    exit;

    //si todo está correcto, se envia un mensaje de exito
} else {
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Usuario registrado con exito'
    ]);
};

//se crea un array con los roles

$roles = [
    1 => 'Administrador',
    2 => 'Comisionado', 
];

//si no existe el rol, se le asigna 'Rol desconocido'

$nombreRol = $roles[$rol] ?? 'Rol desconocido';

//se envia el correo al usuario

$correo = correoCrearUsuario($documento, $nombre, $nombreRol, $apellido, $email, $numero, $usuarioRegistrado);

if (!$correo) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'no se mando el correo'
        ]);
        exit;
    }
    


