<?php

//Indica que la respuesta de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//INCLUSIÓN DE DEPENDENCIAS
require_once "../config/conexion.php";
require_once "../models/getData.php";
require_once "../models/seguridad.php";

//SOLO PERMITIR SOLICITUDES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    //CAPTURA DE CREDENCIALES Y TOKEN DE SEGURIDAD
    $documentoInseguro = $_POST['documento'] ?? '';
    $contrasena = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // VALIDACIÓN DEL TOKEN CSRF
    if (!validarCsrfToken($csrf_token)){
        session_destroy();
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error de seguridad, recargue la pagina'
        ]);
    }

    // VERIFICACIÓN DE QUE LOS CAMPOS NO ESTÉN VACÍOS
    if ($documentoInseguro && $contrasena) {

        //LIMPIEZA DEL DOCUMENTO ANTES DE PROCESAR
        $documento = limpiar($documentoInseguro);

        //SE GUARDA EL LOGIN QUE LLAMA LA FUNCION EN LA VARIABLE VERIFICACION
        $verificacion = loginUsuario($pdo, $documento, $contrasena);
        //SI SE ENCONTRO UN USUARIO EN LA BASE DE DATOS STATUS ES OK
        if ($verificacion['status'] === 'ok') {
            
            //VALIDACIÓN DE ROL: COMISIONADO
            if ($verificacion['data']['id_rol'] == 2) {

                //REGENERACIÓN DE ID DE SESIÓN
                session_regenerate_id(true);
                
                //CONFIGURACIÓN DE VARIABLES DE SESIÓN DEL USUARIO
                $_SESSION['user'] = [
                    'documento' => $verificacion['data']['documento'],
                    'id_rol' => $verificacion['data']['id_rol']
                ];

                //MARCA DE TIEMPO PARA CONTROL DE INACTIVIDAD
                $_SESSION['ultima_actividad'] = time();

                //ELIMINAR EL TOKEN CSRF USADO
                unset($_SESSION['csrf_token']);
                
                //RESPUESTA EXITOSA Y REDIRECCIÓN A VISTA DE ADMINISTRADOR
                echo json_encode([
                    'status' => 'ok',
                    'redirect' => '../../app/views/comisionado/home.php'
                ]);
                exit;
            } else {
                // FALLO: El usuario existe pero no tiene el rol de administrador.
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'No eres Comisionado'
                ]);
            }
        } else {
             // FALLO: Las credenciales no coinciden.
             echo json_encode([
                 'status' => 'error',
                 'mensaje' => 'Credenciales Invalidas'
             ]);
        }
    } else {
        // FALLO: Se enviaron valores vacíos.
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'valores vacios'
        ]);
    }
}
