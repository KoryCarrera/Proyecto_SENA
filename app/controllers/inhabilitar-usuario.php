<?php
require_once "../models/disableData.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>inhabilitar Usuario</title>
</head>

<body>
  <form action="" method="POST">
    <label for="">Documento</label>
    <input type="text" name="documento" id="documento">
    <button type="submit" class="btn-usuario" id="btn-usuario">inhabilitar Usuario</button>
  </form>
  <?php

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
  ?>
</body>

</html>
