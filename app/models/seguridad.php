<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function validarCsrfToken($token)
{
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if(empty($token) || empty($tokenSesion)) {
        return false;
    }

    if(hash_equals($tokenSesion, $token)) {
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

function limpiar($data)
{
    return htmlentities(trim($data), ENT_QUOTES, 'UTF-8');
}

?>