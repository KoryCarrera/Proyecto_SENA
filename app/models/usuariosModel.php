 <?php
    class UsuariosModdel extends baseHelper
    {
        public function cambiarEstadoUsuario($documentoFind, $nuevoEstado, $documentoSession)
        {

            if ($documentoFind == $documentoSession) {
                throw new Exception('¡Por seguridad no se puede desactivar a uno mismo!');
            };

            try {

                $data = [
                    ['value' => $documentoFind, 'type' => PDO::PARAM_STR],
                    ['value' => $nuevoEstado, 'type' => PDO::PARAM_INT],
                ];

                $cambioEstado = parent::insertOrUpdateData('sp_cambiar_estado_usuario(?, ?)', $data);

                return true;
            } catch (Exception $e) {

                error_log('Ha ocurrido un error SQL a la hora de cambiar estado usuario: ' . $e->getMessage());

                throw new Exception($e->getMessage());
            }
        }

        public function configuracionPerfilUsuario($documento, $oldPass, $nombre = null, $apellido = null, $correo = null, $tlf = null, $newPass = null)
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

        public function crearUsuario($documento, $nombre, $apellido, $email, $numero, $id_rol)
        {

            $token = bin2hex(random_bytes(5));

            $contraseñaHash = password_hash($token, PASSWORD_BCRYPT);

            $data = [
                ['value' => $documento, 'type' => PDO::PARAM_STR],
                ['value' => $nombre, 'type' => PDO::PARAM_STR],
                ['value' => $apellido, 'type' => PDO::PARAM_STR],
                ['value' => $email, 'type' => PDO::PARAM_STR],
                ['value' => $id_rol, 'type' => PDO::PARAM_INT],
                ['value' => $contraseñaHash, 'type' => PDO::PARAM_STR],
                ['value' => $numero, 'type' => PDO::PARAM_STR]
            ];

            try {
                parent::insertOrUpdateData('sp_registrar_usuario(?, ?, ?, ?, ?, ?, ?)', $data);

                return $token;
            } catch (Exception $e) {
                error_log('Error al crear usuario: ' . $e->getMessage());
                throw new Exception($e);
            }
        }

        public function editarUsuario(
            $documento,
            $nombre = null,
            $apellido = null,
            $email = null,
            $numero = null,
            $id_rol = null,
            $refreshPass = false
        ) {

            $dataFUser = [
                ['value' => $documento, 'type' => PDO::PARAM_STR]
            ];

            $findUSer = parent::consultSimpleWithParams('sp_traer_usuario(?)', $dataFUser);

            if (!$findUSer) {
                throw new Exception('¡No se ha podido encontrar el usuario!');
            };

            $passToSave = null;

            if ($refreshPass !== false) {
                $token = bin2hex(random_bytes(5));
                $passToSave = password_hash($token, PASSWORD_BCRYPT);
            };

            $dataInsert = [
                ['value' => $documento, 'type' => PDO::PARAM_STR],
                ['value' => $nombre, 'type' => PDO::PARAM_STR],
                ['value' => $apellido, 'type' => PDO::PARAM_STR],
                ['value' => $email, 'type' => PDO::PARAM_STR],
                ['value' => $numero, 'type' => PDO::PARAM_STR],
                ['value' => $id_rol, 'type' => PDO::PARAM_INT],
                ['value' => $passToSave, 'type' => PDO::PARAM_STR],
            ];

            try {

                parent::insertOrUpdateData('sp_gestionar_usuario(?, ?, ?, ?, ?, ?, ?)', $dataInsert);
            } catch (Exception $e) {
                error_log('Error al gestionar usuario: ' . $e->getMessage());
                throw new Exception($e);
            }
        }

        public function auth2FA($documento, $codigo)
        {

            $dataUser = [
                ['value' => $documento, 'type' => PDO::PARAM_STR],
            ];

            $findUser = parent::consultSimpleWithParams('sp_traer_usuario(?)', $dataUser);

            if (!$findUser) {
                throw new Exception('¡Usuario no encontrado¡');
            };

            if (!$findUser['2FA']) {
                throw new Exception('¡Usuario no desea 2FA!');
            };

            $consultToken = parent::consultSimpleWithParams('sp_consultar_token_2fa(?)', $dataUser);

            if ($consultToken !== $codigo) {
                throw new Exception('¡Codigo de 2FA invalido!');
            };

            return true;
        }

        public function loginUsuario($documento, $contrasena)
        {

            $finData = [
                ['value' => $documento, 'type' => PDO::PARAM_STR],
            ];

            $findUser = parent::consultSimpleWithParams('sp_login_usuario(?)', $finData);

            if (!$findUser) {
                throw new Exception('¡Documento invalido!');
            };

            if (!password_verify($contrasena, $findUser['contraseña'])) {
                throw new Exception('¡Contraseña incorrecta!');
            }

            if (!$findUser['2FA']) {
                return [
                    ['2FA' => false]
                ];
            };

            return [
                ['2FA' => true]
            ];
        }

        public function generarCookie($documento, $Auth2fa)
        {

            if ($Auth2fa !== true) {
                return false;
            };

            $identToken = bin2hex(random_bytes(10));

            $expirationToken = time() + (30 * 24 * 60 * 60);

            setcookie(
                'device_id', // Nombre de la cookie
                $identToken, // Valor del token
                $expirationToken, // Tiempo de vida
                '/', // Ruta (disponible en todo el sitio)
                '', // Dominio (vacío para el actual)
                true, // Secure (Solo enviar por HTTPS)
                true // HttpOnly (No accesible por JS)
            );

            try {

                $dataToken = [
                    ['value' => $documento, 'type' => PDO::PARAM_STR],
                    ['value' => $identToken, 'type' => PDO::PARAM_STR]
                ];

                parent::insertOrUpdateData('sp_guardar_cookie(?, ?)', $dataToken);
            } catch (Exception $e) {
                error_log('Error al guardar la cookies en la base de datos: ' . $e->getMessage());

                throw new Exception('Ha ocurrido un error con la cookie: ' . $e->getMessage());
            }
        }


        public function validarDispositivo($documento)
        {
            // Verificar si la cookie existe
            if (!isset($_COOKIE['device_id'])) {
                return false;
            } else {
                $tokenCookie = $_COOKIE['device_id'];

                $consultData = [
                    [ 'value' => $documento, 'type' => PDO::PARAM_STR]
                ];

                // Consultar en la DB si ese token está asociado a este usuario
                $dispositivo = parent::consultObjectWithParams('sp_consultar_token_cookie(?)', $consultData);

                if ($dispositivo['cookie'] != $tokenCookie){
                    return false;
                };
            }
        }
    }
