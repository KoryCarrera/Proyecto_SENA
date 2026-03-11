<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

session_start();

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "//../models/insertData.php";
require_once __DIR__ . "/../models/baseHelper.php";
require_once __DIR__ . "/../models/usuariosModel.php";

//matamos el script con exit para matar el codigo en cada validacion incorrecta
//validamos que el metodo sea post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}

//capturamos los datos del usuario
$documento = $_POST['documento'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$numero = $_POST['telefono'];
$rol = $_POST['rol'];

try {
    $model = new UsuariosModdel($pdo);

    
    //validacion de datos
if(!$documento || !$nombre || !$apellido || !$email || !$numero || !$rol ) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Todos los datos son requeridos'
    ]);
    exit;
};

if (!is_numeric($rol)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Valor de rol no valido'
    ]);
    exit;
} 
} catch (Exception $e) {
    error_log('Error al crear usuario: ' . $e->getMessage());
    throw new Exception($e);
}

if(!is_string($nombre) || !is_string($apellido) || !is_string($email) || !is_string($numero)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Datos no validos'
    ]);
    exit;
}
//si todo está verdadero insertamos el usuario
$usuarioRegistrado = $model->crearUsuario( $documento,  $nombre, $apellido, $email, $numero, $rol);

if(!$usuarioRegistrado) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al registrar al usuario'
    ]);
    exit;

    
} else {
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Usuario registrado con exito'
    ]);
};

$roles = [
    1 => 'Administrador',
    2 => 'Comisionado', 
];

$nombreRol = $roles[$rol] ?? 'Rol desconocido';

$asunto = "Nuevo Usuario Registrado - #{$documento}: {$nombre}";

      $cuerpoHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #28a745; color: white; padding: 15px; }
            .content { padding: 20px; }
            .detalle { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            .label { font-weight: bold; width: 150px; background-color: #e9ecef; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Notificación de Nuevo Usuario</h2>
        </div>
        <div class='content'>
            <p>Se ha registrado un nuevo caso en el sistema:</p>
            <div class='detalle'>
                <table>
                    <tr>
                        <td class='label'>Documento del usuario creado:</td>
                        <td><strong>{$documento}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Rol del usuario:</td>
                        <td>{$nombreRol}</td>
                    </tr>
                    <tr>
                        <td class='label'>Nombre:</td>
                        <td>{$nombre}</td>
                    </tr>
                    <tr>
                        <td class='label'>Apellido:</td>
                        <td>{$apellido}</td>
                    </tr>
                    <tr>
                        <td class='label'>Email:</td>
                        <td>{$email}</td>
                    </tr>
                    <tr>
                        <td class='label'>Número:</td>
                        <td>{$numero}</td>   
                    </tr>
                    <tr>
                        <td class='label'><strong>Su contraseña:</strong></td>
                        <td><strong>{$usuarioRegistrado}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Fecha de registro:</td>
                        <td>" . date('d/m/Y H:i:s') . "</td>
                    </tr>
                </table>
            </div>";

      $cuerpoHTML .= "
            <p style='margin-top: 20px;'>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </body>
    </html>";

    $cuerpoAlt = "NUEVO USUARIO REGISTRADO\n" .
                 "=====================\n\n" .
                 "Documento del usuario: {$documento}\n" .
                 "Rol: {$nombreRol}\n" .
                 "Nombre: {$nombre}\n" .
                 "Apellido: {$apellido}\n" .
                 "Email: {$email}\n" .
                 "Numero: {$numero}\n" .
                 "Fecha: " . date('d/m/Y H:i:s') . "\n\n" .
                 "Este es un mensaje automático.";


     $destinatarios = [
        [
            'emailUser' => $email, 
            'userName' => $nombre
        ]
     ];
        


    $correoEnviado = enviarCorreo(
        $asunto,                    
        $cuerpoHTML,              
        $cuerpoAlt,              
        $destinatarios,         
        null,                        
        null                         
    );

      if ($correoEnviado) {
        error_log(" Correo enviado para usuario #{$documento}");
    } else {
        error_log(" No se pudo enviar correo para usuario #{$documento}");
    }
    


