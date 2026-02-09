<?php
//Definimos el tipo de archivo que llegará y enviará
header('Content-Type: application/json');

//Inclusión de dependencias
require_once __DIR__ . "/../models/disableData.php";
require_once __DIR__ . "/../config/conexion.php";

//Validamos protocolo http
if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  echo json_encode([
    'status' => 'error',
    'mensaje' => 'Metodo no permitido'
  ]);
  exit;
};

$documento = $_POST['documento'];
$estado = $_POST['estado'];

if (!$documento && !$estado) {

  echo json_encode([
    'status' => 'error',
    'mensaje' => 'Valores vacios'
  ]);

  exit;
}

$usuarioInhabilitado = cambiarEstadoUsuario($pdo, $documento, $estado);

if (!$usuarioInhabilitado) {
  echo json_encode([
    'status' => 'error',
    'mensaje' => 'Error al cambiar estado del usuario'
  ]);
  exit;
} else {
  echo json_encode([
    'status' => 'ok',
    'mensaje' => 'Estado de Usuario Cambiado'
  ]);
}
