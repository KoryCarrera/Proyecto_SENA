<?php

// INCLUSIÓN DE SEGURIDAD 
require_once "../models/seguridad.php";

// SOLO PERMITIR SOLICITUDES POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CAPTURA DE VARIABLES DEL POST
    $logout = $_POST["logout"] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if ($logout) {

        // VALIDACIÓN DEL TOKEN 
        if (!validarCsrfToken($csrf_token)) {
            // Falla la seguridad: destruyo sesión y me voy al inicio.
            session_destroy();
            header("Location: /Proyecto_SENA/Public/index.php");
            exit;
        }

        // CIERRE Y DESTRUCCIÓN DE LA SESIÓN
        session_unset();  // Elimino las variables
        session_destroy(); // Destruyo la sesión completamente
        
        // REDIRECCIÓN FINAL AL INICIO
        header("Location: /Proyecto_SENA/Public/index.php");
        exit;
    }
    
} else {
    // ERROR 405: MÉTODO HTTP NO PERMITIDO
    echo 'HTTP/1.1 405 Method Not Allowed';
    exit;
}
?>
