<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";
require_once "../models/seguridad.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentoInseguro = $_POST['documento'] ?? '';
    $contrasena = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validarCsrfToken($csrf_token)){
        session_destroy();
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error de seguridad, recargue la pagina'
        ]);
    }

    if ($documentoInseguro && $contrasena) {

        $documento = limpiar($documentoInseguro);

        $verificacion = loginUsuario($pdo, $documento, $contrasena);
        if ($verificacion['status'] === 'ok') {
            if ($verificacion['data']['id_rol'] == 2) {

                session_regenerate_id(true);
                
                $_SESSION['user'] = [
                    'documento' => $verificacion['data']['documento'],
                    'id_rol' => $verificacion['data']['id_rol']
                ];

                $_SESSION['ultima_actividad'] = time();

                unset($_SESSION['csrf_token']);
                echo json_encode([
                    'status' => 'ok',
                    'redirect' => '../../app/views/comisionado/home.php'
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'No eres comisionado'
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
