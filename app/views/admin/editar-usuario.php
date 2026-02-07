<?php
require_once __DIR__ . "/../../models/updateData.php";
require_once __DIR__ . "/../../controllers/checkSessionAdmin.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Usuario</title>
</head>

<body>
    <form action="" method="POST">
        <label for="">Documento</label>
        <input type="text" name="documento" id="documento">
        <label for="">Nombre</label>
        <input type="text" name="nombre" id="nombre"><br>
        <label for="">Apellido</label>
        <input type="text" name="apellido" id="apellido"><br>
        <label for="">Email</label>
        <input type="text" name="email" id="email"><br>
        <select name="rol" id="rol">
            <option selected disabled>Seleccione un Rol</option>
            <option value="1">Administrador</option>
            <option value="2">Comisionado</option>
        </select>
        <button type="submit" class="btn-usuario" id="btn-usuario">siguiente</button>
    </form>
    <a href="inhabilitar-usuario.php">inhabilitar Usuario</a>
</body>

</html>