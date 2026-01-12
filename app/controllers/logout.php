<?php

// INCLUSIÓN DE SEGURIDAD 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SOLO PERMITIR SOLICITUDES POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CAPTURA DE VARIABLES DEL POST
    $logout = $_POST["logout"] ?? '';

    if ($logout) {

        // CIERRE Y DESTRUCCIÓN DE LA SESIÓN
        $_SESSION = array();
        session_unset();  // Elimino las variables
        session_destroy(); // Destruyo la sesión completamente

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        // REDIRECCIÓN FINAL AL INICIO
        header("Location: /");
        exit;
    }
} else {
    // ERROR 405: MÉTODO HTTP NO PERMITIDO
    echo 'HTTP/1.1 405 Method Not Allowed';
    exit;
}
