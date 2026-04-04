<?php
//Definimos el tipo de archivo que llegará y enviará
header('Content-Type: application/json');

session_start();

//Inclusión de dependencias

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";



//Validamos protocolo http
if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  echo json_encode([
    'status' => 'error',
    'mensaje' => 'Metodo no permitido'
  ]);
  exit;
};

//se capturan los datos del usuario

$documentoSession = $_SESSION['user']['documento'];
$documento = $_POST['documento'];
$estado = $_POST['estado'];
$motivo = $_POST['motivo'];

//se valida que los datos no sean nulos,de serlo,se envia un mensaje de error

if (!$documento && !$estado && $motivo) {

  echo json_encode([
    'status' => 'error',
    'mensaje' => 'Valores vacios'
  ]);

  exit;
}

//usamos try catch para manejar posibles errores de conexion a la base de datos

try {
  $model = new UsuariosModdel($pdo);

  //se cambia el estado del usuario

  $model->cambiarEstadoUsuario($documento, $estado, $documentoSession, $motivo);

  //de ser exitoso se envia un mensaje de exito

  echo json_encode([
    'status' => 'ok'
  ]);

  //manejo de errores

} catch (Exception $e) {
  error_log('Ha ocurrido un error SQL a la hora de cambiar estado usuario: ' . $e->getMessage());

    echo json_encode([
    'status' => 'error',
    'mensaje' => $e->getMessage()
  ]);

  //se lanza la excepcion

  throw new Exception($e->getMessage());
}
