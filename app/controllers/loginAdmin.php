<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['documento'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    if ($documento && $contrasena) {
        $verificacion = loginUsuario($pdo, $documento, $contrasena);
        if ($verificacion['status'] === 'ok') {
            if ($verificacion['data']['id_rol'] == 1) {
                 echo json_encode([
                    'status' => 'ok',
                    'redirect' => '../../app/views/admin/home.php'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'No eres administrador'
                ]);
            }
        } else {
             echo json_encode([
                'status' => 'error',
                'mensaje' => 'Credenciales Invalidas'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'valores vacios'
        ]);
    }
}
