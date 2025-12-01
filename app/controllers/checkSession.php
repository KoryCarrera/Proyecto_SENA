<?php
// Define el tiempo máximo de inactividad en segundos (ej. 5 minutos = 300 segundos)
define('TIEMPO_MAXIMO_INACTIVIDAD', 300); 

// === 1. INICIO DE SESIÓN Y BLOQUEO DE CACHÉ ===

// Inicia la sesión si aún no está activa para poder acceder a $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Bloqueo de Caché del Navegador
// CRÍTICO: Esto previene que el usuario vea la página guardada en caché al usar el botón "Atrás" 
// después de cerrar sesión o ser expulsado por inactividad.
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies/Caché

// === 2. VERIFICACIÓN DE AUTENTICACIÓN ===

// Comprueba si la variable de sesión 'admin_user' existe (es decir, si el usuario se logeó exitosamente)
if (!isset($_SESSION['user'])) {
    
    // Si NO está logeado, lo redirigimos inmediatamente al login
    // AJUSTA ESTA RUTA si la ubicación de tu login.php es diferente
    header('Location: ../../../Public/index.php'); 
    exit; // Detiene la ejecución del resto del script (y no muestra el contenido de la página)
}

// === 3. CONTROL DE TIEMPO POR INACTIVIDAD ===

// Verifica si la sesión tiene registrada la hora de la última actividad
// Y si ha pasado más tiempo del límite permitido
if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > TIEMPO_MAXIMO_INACTIVIDAD)) {
    
    // Si la sesión ha expirado por inactividad:
    session_unset();    // Elimina todas las variables de sesión (admin_user, etc.)
    session_destroy();  // Destruye el archivo de sesión del servidor
    
    // Redirigir al login (con un parámetro para indicar que fue por timeout)
    header('Location: ../../../Public/index.php?timeout=1');
    exit;
}

// === 4. ACTUALIZACIÓN DE ACTIVIDAD ===

// Si el usuario pasó todas las verificaciones (está logeado y activo), 
// actualizamos la hora de su última actividad para "reiniciar" el contador de 30 minutos.
$_SESSION['ultima_actividad'] = time();

// Si el script llega hasta aquí, significa que el usuario es un administrador válido y activo.