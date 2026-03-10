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

$documentoSession = $_SESSION['user']['documento'];
$documento = $_POST['documento'];
$estado = $_POST['estado'];

if (!$documento && !$estado) {

  echo json_encode([
    'status' => 'error',
    'mensaje' => 'Valores vacios'
  ]);

  exit;
}

try {
  $model = new UsuariosModdel($pdo);


  $model->cambiarEstadoUsuario($documento, $estado, $documentoSession);

  echo json_encode([
    'status' => 'ok'
  ]);
} catch (Exception $e) {
  error_log('Ha ocurrido un error SQL a la hora de cambiar estado usuario: ' . $e->getMessage());

  throw new Exception($e->getMessage());
}
