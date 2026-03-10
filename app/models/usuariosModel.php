<?php
class UsuariosModdel extends baseHelper
{
    public function cambiarEstadoUsuario($documentoFind, $nuevoEstado, $documentoSession)
    {

        if($documentoFind == $documentoSession){
            throw new Exception('¡Por seguridad no se puede desactivar a uno mismo!');
        };

        try {

            $data = [
                ['value' => $documentoFind, 'type' => PDO::PARAM_STR],
                ['value' => $nuevoEstado, 'type' => PDO::PARAM_INT],
            ];

            $cambioEstado = parent::insertOrUpdateData('sp_cambiar_estado_usuario', $data);

            if (!$cambioEstado) {
                throw new Exception('¡Ha ocurrido un error a la hora de cambiar el estado del usuario!');
            };

            return true;

        } catch (Exception $e) {

            error_log('Ha ocurrido un error SQL a la hora de cambiar estado usuario: ' . $e->getMessage());

            throw new Exception($e->getMessage());
        }
    }

    public function configuracionPerfilUsuario($nombre = null, $apellido = null, $correo = null, $newPass = null, $tlf = null, $oldPass, $documento)
    {
        $data = [
            ['value' => $documento, 'type' => PDO::PARAM_STR]
        ];

        $userVerify = parent::consultSimpleWithParams('sp_login_usuario(?)', $data);

        if (!$userVerify) {
            throw new Exception('¡No se ha podido encontrar el usuario logueado!');
        }

        if (!password_verify($oldPass, $userVerify['contraseña'])) {
            throw new Exception('¡Contraseña invalida, trata de nuevo!');
        }

        $passToSave = $newPass ? password_hash($newPass, PASSWORD_BCRYPT) : null;

        $newData = [
            ['value' => $documento, 'type' => PDO::PARAM_STR],
            ['value' => $nombre, 'type' => PDO::PARAM_STR],
            ['value' => $apellido, 'type' => PDO::PARAM_STR],
            ['value' => $correo, 'type' => PDO::PARAM_STR],
            ['value' => $passToSave, 'type' => PDO::PARAM_STR],
            ['value' => $tlf, 'type' => PDO::PARAM_STR],
        ];

        try {

            parent::insertOrUpdateData('sp_configurar_usuario(?, ?, ?, ?, ?, ?)', $newData);
        } catch (Exception $e) {
            error_log('Error al configurar perfil de usuario: ' . $e->getMessage());
            throw new Exception($e);
        }
    }
}
