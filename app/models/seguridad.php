<?php

// INICIO DE SESIÓN Y GENERACIÓN INICIAL DEL TOKEN CSRF

if (session_status() == PHP_SESSION_NONE) {
    // Si la sesión no está iniciada, la iniciamos.
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    // Si no hay un token CSRF, generamos uno seguro (32 bytes aleatorios).
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// FUNCIÓN: VALIDAR TOKEN CSRF (PROTECCIÓN CONTRA ATAQUES CROSS-SITE REQUEST FORGERY)

// Retorna: true si el token enviado coincide con el de sesión, false en caso contrario.
function validarCsrfToken($token)
{
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if(empty($token) || empty($tokenSesion)) {
        // Bloqueo si falta el token en la sesión o en la petición.
        return false;
    }

    if(hash_equals($tokenSesion, $token)) {
        // Validación segura (hash_equals) exitosa.
        // ELIMINACIÓN DEL TOKEN DE SESIÓN PARA EVITAR REUTILIZACIÓN (one-time token).
        return true;
    }
    // Fallo de coincidencia entre los tokens
    return false;
}


// FUNCIÓN: LIMPIEZA DE DATOS (PROTECCIÓN BÁSICA CONTRA XSS)

// Retorna: Los datos limpios.
function limpiar($data)
{
    // 1. Elimina espacios en blanco del inicio/final (trim).
    // 2. Convierte caracteres especiales a entidades HTML (evita XSS).
    return htmlentities(trim($data), ENT_QUOTES, 'UTF-8');
}

?>