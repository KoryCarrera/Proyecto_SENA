<?php
// ============================================================
// verArchivo.php — Puente seguro para servir archivos de uploads
// ============================================================

// Desactivar errores para evitar que rompan el stream binario
error_reporting(0);
ini_set('display_errors', 0);

// 1. Verificar que el usuario tenga sesión activa
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit('Acceso no autorizado');
}

// 2. Obtener y sanear el parámetro de ruta
$rutaRelativa = $_GET['ruta'] ?? '';

if (empty($rutaRelativa)) {
    http_response_code(400);
    exit('Parámetro de ruta requerido');
}

// 3. Construir la ruta absoluta y proteger contra path traversal
$baseUpload = realpath(__DIR__ . '/../uploads');
if (!$baseUpload) {
    http_response_code(500);
    exit('Error de configuración del servidor (uploads not found)');
}

// Limpiar la ruta relativa para evitar path traversal básico (../)
$rutaLimpia = str_replace(['../', '..\\'], '', $rutaRelativa);
$rutaLimpia = str_replace('uploads/', '', $rutaLimpia);
$rutaLimpia = ltrim($rutaLimpia, '/');

$rutaFinal = $baseUpload . DIRECTORY_SEPARATOR . $rutaLimpia;

// 4. Verificar que el archivo exista físicamente y esté dentro de la base
if (!file_exists($rutaFinal) || is_dir($rutaFinal)) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// Validación de seguridad final: el realpath final debe empezar por $baseUpload
$rutaRealFinal = realpath($rutaFinal);
if (!$rutaRealFinal || strpos($rutaRealFinal, $baseUpload) !== 0) {
    http_response_code(403);
    exit('Acceso denegado');
}

// 5. Determinar el tipo MIME según la extensión
$extension = strtolower(pathinfo($rutaRealFinal, PATHINFO_EXTENSION));

$mimeTypes = [
    // Imágenes
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
    // Documentos
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt'  => 'text/plain',
    // Video
    'mp4'  => 'video/mp4',
];

$mime = $mimeTypes[$extension] ?? 'application/octet-stream';

// 6. Decidir si el archivo se muestra en el navegador (inline) o se descarga
$mostrarInline = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'mp4', 'txt'];
$disposicion   = in_array($extension, $mostrarInline) ? 'inline' : 'attachment';

$nombreOriginal = basename($rutaRealFinal);

// 7. Enviar headers y el contenido del archivo
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposicion . '; filename="' . $nombreOriginal . '"');
header('Content-Length: ' . filesize($rutaRealFinal));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

// Limpiar cualquier buffer de salida previo
ob_end_clean();

// Enviar el archivo en chunks para no agotar memoria con archivos grandes
$handle = fopen($rutaRealFinal, 'rb');
while (!feof($handle)) {
    echo fread($handle, 8192);
    flush();
}
fclose($handle);
exit;
