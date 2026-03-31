<?php
header('Content-Type: application/json');

//se define el formato de respuesta y peticion de con la que trabajamos (json)

session_start();

//se inicia la sesion para poder acceder a los datos del usuario

require_once __DIR__ . '/../config/conexion.php'; 
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";

//llamamos las dependencias necesarias para el funcionamiento del controlador

//se valida que el metodo sea POST y si no simplemente finaliza el codigo 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;

    //tomamos los datos del usuario a partir de la sesion y del metodo post
}
$documento = $_SESSION['user']['documento'];
$confirmar_contraseña = $_POST['password_actual'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$contraseña = $_POST['contrasena'] ?? '';
$numero = $_POST['numero'] ?? '';

//usamos try catch para manejar posibles errores de conexion a la base de datos

try {

    //se crea una instancia de la clase UsuariosModdel

    $model = new UsuariosModdel($pdo);

    // Declaramos variable
    $resultado = $model->configuracionPerfilUsuario($documento, $confirmar_contraseña, $nombre, $apellido, $email, $numero, $contraseña);

    // Actualizar sesión para reflejar cambios 
    if (!empty($_POST['nombre'])) $_SESSION['user']['nombre'] = $_POST['nombre'];
    if (!empty($_POST['apellido'])) $_SESSION['user']['apellido'] = $_POST['apellido'];
    if (!empty($_POST['email'])) $_SESSION['user']['email'] = $_POST['email'];

    //se envia un mensaje de exito por medio de un json

    echo json_encode([
        'status' => 'ok', 
        'mensaje' => 'Información actualizada correctamente.'
    ]);

    //manejo de errores

} catch (Exception $e) {
  error_log('Ha ocurrido un error a la hora de configurar el perfil del usuario: ' . $e->getMessage());

  //se crea una excepcion

  throw new Exception($e->getMessage());
} 

