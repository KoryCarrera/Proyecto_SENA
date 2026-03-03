<?php
require_once __DIR__ . "/../models/updateData.php";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {

    $documento = $_POST["documento"];
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $email = $_POST["email"];
    $rol = $_POST["rol"];

    if ($documento && $nombre && $apellido && $email && $rol) {
        $Actualizar = ActualizarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol);
        if ($Actualizar) {
            echo "usuario actualizado con exito";
        } else {
            echo "error al actualizar usuario";
        }
    } else {
        echo "ingrese todos los datos";
    }
}
