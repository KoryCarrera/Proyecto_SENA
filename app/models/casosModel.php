<?php

use Safe\Exceptions\ExecException;

require_once __DIR__ . '/baseHelper.php';
class CasosModel extends baseHelper
{

    public function cambiarEstadoCaso($nuevoEstado, $documentUser, $id_caso, $motivo)
    {

        try {

            $userData = [
                ['value' => $documentUser, 'type' => PDO::PARAM_STR]
            ];
            $caseData = [
                ['value' => $id_caso, 'type' => PDO::PARAM_INT]
            ];

            $findUser = parent::consultSimpleWithParams("sp_traer_usuario(?)", $userData);
            $findCase = parent::consultSimpleWithParams("sp_obtener_caso_por_id(?)", $caseData);

            if (!$findUser) {
                throw new Exception("¡No se ha encontrado el usuario que desea cambiar el estado!");
            }

            if ($findCase['estado'] === $nuevoEstado) {
                throw new Exception('es el mismo estado que intentas asignar');
            }

            if ($findCase['estado'] == 'Por asignar') {
                throw new Exception('No puedes gestionar un caso con estado Por asignar');
            }

            if ($findCase['estado'] == 'Atendido' || $findCase['estado'] == 'No atendido' || $nuevoEstado == 1 || $nuevoEstado == 3) {

                $newData = [
                    ['value' => $id_caso, 'type' => PDO::PARAM_INT],
                    ['value' => $nuevoEstado, 'type' => PDO::PARAM_INT],
                    ['value' => $documentUser, 'type' => PDO::PARAM_STR],
                    ['value' => $motivo, 'type' => PDO::PARAM_STR],
                ];

                parent::insertOrUpdateData('sp_actualizar_estado_caso_con_motivo(?, ?, ?, ?)', $newData);

                return true;
            }

            $newData = [
                ['value' => $id_caso, 'type' => PDO::PARAM_INT],
                ['value' => $nuevoEstado, 'type' => PDO::PARAM_INT],
                ['value' => $documentUser, 'type' => PDO::PARAM_STR]
            ];

            parent::insertOrUpdateData('sp_actualizar_estado_caso(?, ?, ?)', $newData);

            return true;
        } catch (Exception $e) {
            error_log('Ha ocurrido un error a la hora de cambiar el estado de caso: ' . $e->getMessage());
            throw new Exception('¡Ha ocurrido un error a la hora de cambiar el estado del caso! ' . $e->getMessage());
        }
    }

    public function reasignarCaso($documentoUser, $documento, $idCaso, $motivo)
    {

        try {

            $dataUser = [
                ['value' => $documentoUser, 'type' => PDO::PARAM_STR]
            ];

            $dataCase = [
                ['value' => $idCaso, 'type' => PDO::PARAM_STR]
            ];

            $newUser = [
                ['value' => $documento, 'type' => PDO::PARAM_STR]
            ];

            $findUser = parent::consultSimpleWithParams('sp_traer_usuario(?)', $dataUser);
            $findCase = parent::consultSimpleWithParams('sp_obtener_caso_por_id(?)', $dataCase);
            $findNewComi = parent::consultSimpleWithParams('sp_traer_usuario(?)', $newUser);

            if (!$findNewComi) {
                throw new Exception('¡No se ha podido encontrar al usuario que deseas asignar el caso!');
            };

            if ($findNewComi['id_rol'] != 2) {
                throw new Exception('¡Solo se puede reasignar casos a usuarios con el rol de comisionado!');
            }

            if (!$findUser) {
                throw new Exception('¡No se ha podido encontrar al usuario que desea hacer la reasignación!');
            };

            if (!$findCase) {
                throw new Exception('¡No se ha podido encontrar el caso que se desea gestionar!');
            };

            if ($findCase['estado'] == 'Atendido') {
                throw new Exception('¡No puedes reasginar un caso ya atendido!');
            };

            if ($findUser['id_rol'] != 1) {
                throw new Exception('¡No tienes permisos para esta acción!');
            };

            $reasignarCaso = [
                ['value' => $documentoUser, 'type' => PDO::PARAM_STR],
                ['value' => $documento, 'type' => PDO::PARAM_STR],
                ['value' => $idCaso, 'type' => PDO::PARAM_INT],
                ['value' => $motivo, 'type' => PDO::PARAM_STR],
                ['value' => $findCase['documento'], 'type' => PDO::PARAM_STR],
            ];

            $reasignar = parent::insertOrUpdateData('sp_reasignar_caso(?, ?, ?, ?, ?)', $reasignarCaso);

            if ($reasignar || count($reasignar) > 0) {
                return true;
            } else {
                throw new Exception('No se pudo hacer la reasignación');
            }

            return true;
        } catch (Exception $e) {
            error_log('Ha ocurrido un error al reasignar caso: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function registrarCaso($documento, $proceso, $tipoCaso, $descripcion, $nombre, $radicado)
    {

        try {
            $data = [
                ['value' => $documento, 'type' => PDO::PARAM_STR],
                ['value' => $proceso, 'type' => PDO::PARAM_INT],
                ['value' => $tipoCaso, 'type' => PDO::PARAM_INT],
                ['value' => $descripcion, 'type' => PDO::PARAM_STR],
                ['value' => $nombre, 'type' => PDO::PARAM_STR],
                ['value' => $radicado, 'type' => PDO::PARAM_STR]
            ];

            $casoRegistrado = parent::insertOrUpdateData('sp_registrar_caso(?, ?, ?, ?, ?, ?)', $data);

            if (!$casoRegistrado) {
                throw new Exception('No se pudo obtener la confirmación del registro desde la base de datos.');
            }

            return ['success' => true, 'data' => $casoRegistrado];
        } catch (Exception $e) {
            error_log('Ha ocurrido un error al registrar caso: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function registrarSeguimiento($observacion, $idCaso, $documento)
    {

        try {
            $data = [
                ['value' => $observacion, 'type' => PDO::PARAM_STR],
                ['value' => $idCaso, 'type' => PDO::PARAM_INT],
                ['value' => $documento, 'type' => PDO::PARAM_STR]
            ];

            $sp = 'sp_registrar_seguimiento(?, ?, ?)';

            $nuevoSeguimiento = parent::insertOrUpdateData($sp, $data);

            if (!$nuevoSeguimiento) {
                throw new Exception('No se pudo registrar el nuevo seguimiento en la base de datos');
            }

            return ['success' => true];
        } catch (Exception $e) {
            error_log('Ha occurrido un error al registrar el seguimiento: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function registrarProceso($descripcion, $nombre, $documentoUsuario)
    {

        try {
            $data = [
                ['value' => $descripcion, 'type' => PDO::PARAM_STR],
                ['value' => $nombre, 'type' => PDO::PARAM_STR],
                ['value' => $documentoUsuario, 'type' => PDO::PARAM_STR]
            ];

            $sp = 'sp_registrar_proceso_organizacional(?, ?, ?)';

            $nuevoProceso = parent::insertOrUpdateData($sp, $data);

            if (!$nuevoProceso) {
                throw new Exception('No se pudo crear el nuevo proceso');
            }

            return ['success' => true, 'data' => $nuevoProceso];
        } catch (Exception $e) {
            error_log('Ha occurrido un error al registrar el proceso: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
