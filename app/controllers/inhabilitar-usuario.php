<?php

//INCLUSIÓN DE DEPENDENCIAS
require_once __DIR__ . "/../models/disableData.php";

//Controlador de inhabilitar usuarios

  //Indica que el servidor se comunica enviando POST
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $documento = $_POST["documento"];
    if ($documento) {
      $Inhabilitar = inhabilitarUsuario($pdo, $documento);
      if ($Inhabilitar) {
        echo "Usuario inhabilitado correctamente";
      } else {
        echo "Error al intentar inhabilitar usuario";
      }
    } else {
      echo "Rellene todos los parametros";
    }
  }
