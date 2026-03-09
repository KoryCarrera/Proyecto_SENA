<?php
class UsuariosModdel extends baseHelper
{
    public function cambiarEstadoUsuario($documentoFind, $nuevoEstado, $documentoSession)
    {

        if (!$documentoFind) {
            throw new Exception("¡El documento a encontrar es nulo!");
        };

        if (!$documentoSession){
            throw new Exception("¡No se ha podido validar el usuario logueado!");
        };

        if (!$nuevoEstado){
            throw new Exception('¡No se puede enviar un "null" como nuevo estado!');
        };

        if (!is_numeric($nuevoEstado)) {
            throw new Exception('¡Tipo de dato de nuevo estado no valido');
        };

        if($documentoFind == $documentoSession){
            throw new Exception('¡Por seguridad no se puede desactivar a uno mismo!');
        };

        try{

            $data = [
                'param' => [$documentoFind, $nuevoEstado],
                'type' => [PDO::PARAM_STR, PDO::PARAM_INT]
            ];

            $cambioEstado = parent::insertOrUpdateData('sp_cambiar_estado_usuario', $data);

            if(!$cambioEstado){
                throw new Exception('¡Ha ocurrido un error a la hora de cambiar el estado del usuario!');
            };

        } catch(Exception $e){

        error_log('Ha ocurrido un error SQL a la hora de cambiar estado usuario: '. $e->getMessage());

        throw new Exception($e->getMessage());
        
        }
    }
}
