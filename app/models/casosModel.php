<?php

class CasosModel extends baseHelper {

    public function cambiarEstadoCaso($nuevoEstado, $documentUser, $id_caso){
        
        try {

            $userData = [
                [ 'value' => $documentUser, 'type' => PDO::PARAM_STR]
            ];
            $caseData = [
                ['value' => $id_caso, 'type' => PDO::PARAM_INT]
            ];

            $findUser = parent::consultSimpleHelper("sp_traer_usuario(?)", $userData);
            $findCase = parent::consultSimpleHelper("sp_obtener_caso_por_id(?)" ,$caseData);

            if (!$findUser) {
                throw new Exception("¡No se ha encontrado el usuario que desea cambiar el estado!");
            };

            if ($findCase['estado_caso'] === $nuevoEstado) {
                throw new exepcion ('es el mismo estado que intentas asignar');
            }
            if ($caseData);


        }catch (Exception $e) {
            error_log('Ha ocurrido un error a la hora de cambiar el estado de caso: '. $e->getMessage());
            throw new Exception('¡Ha ocurrido un error a la hora de cambiar el estado del caso! '. $e->getMessage());
        }
    }

}