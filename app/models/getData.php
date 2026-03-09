<?php

function listarCasos($pdo)
{
    $stmt = $pdo->prepare("CALL sp_listar_casos()");

    try {
        $stmt->execute();
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if ($casos) {
            return [
                'status' => 'ok',
                'data' => $casos
            ];
        } else {
            return [
                'status' => 'error',
                'mensaje' => 'Valores vacios'
            ];
        }
    } catch (PDOException $e) {
        error_log('Error al obtener los casos: ' . $e->getMessage());
        return null;
    }
}

function filtrarPorComisionado($pdo, $document)
{
    $stmt = $pdo->prepare("CALL sp_listar_casos_por_comisionado(:documento)");
    $stmt->bindParam(':documento', $document, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $casos;
    } catch (PDOException $e) {
        error_log("Error al filtrar los casos: " . $e->getMessage());
        return null;
    }
}

function obtenerSeguimientosPorCaso($pdo, $idCaso)
{

    $stmt = $pdo->prepare("CALL sp_listar_seguimientos_por_caso(?)");
    $stmt->bindParam(1, $idCaso, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $seguminetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $seguminetos;
    } catch (PDOException $e) {
        error_log("Error al filtrar los seguimientos: " . $e->getMessage());
        return null;
    }
}

function buscarUsuario($pdo, $document)
{
    //Se prepara la sentencia sql a ejecutar
    $stmt = $pdo->prepare("CALL sp_buscar_usuario(?)");
    //Se asignan los parametros necesarios para el sp
    $stmt->bindParam(1, $document, PDO::PARAM_STR);

    //Ejecutamos dentro de un try/catch para manejo de errores
    try {
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC); //Fetch debido a que es un unico registro para hacerlo un objeto y no un array
        $stmt->closeCursor(); //Cerramos el "tunel" con la Bases de datos para evitar errore y sobre cargas
        return $usuario;
    } catch (PDOException $e) {
        error_log("Error al buscar el usuario: " . $e->getMessage()); //Captamos errores en el log del servidor 
        return [
            'status' => 'error',
            'mensaje' => 'Error al buscar el usuario'
        ];
    }
}

function resumenGeneral($pdo)
{
    $stmt = $pdo->prepare("CALL sp_resumen_general()");

    try {
        $stmt->execute();
        $resumenGeneral = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return [
            'status' => 'ok',
            'data' => $resumenGeneral
        ];
    } catch (PDOException $e) {
        error_log("Error al obtener el resumen general: " . $e->getMessage());
        return [
            'status' => 'error',
            'mensaje' => 'Error al obtener el resumen general'
        ];
    }
}

function loginUsuario($pdo, $documento, $contrasena)
{
    error_log("-> DEBUG LOGIN: Intentando login para Documento: " . $documento . " y Contraseña: " . $contrasena);
    //Se alamcenan los errores en el log del servidor
    $stmt = $pdo->prepare("CALL sp_login_usuario(?)"); //Unicamente se verifica el documento debido al encriptamiento de contraseña
    $stmt->bindParam(1, $documento, PDO::PARAM_STR); //Asignamos parametros al sp

    try {
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("-> DEBUG LOGIN: Resultado de fetch() para el usuario: " . print_r($data, true));
        $stmt->closeCursor();

        if ($data && password_verify($contrasena, $data['contraseña'])) { //Validamos que data sea true y password verify tambien
            return [
                'status' => 'ok',
                'mensaje' => 'Usuario válido',
                'data' => $data
            ]; //Retornamos un array asociativo
        } else {
            return [
                'status' => 'error',
                'mensaje' => 'No se ha encontrado el usuario, credenciles invalidas'
            ]; //Retornamos error en caso de algun dato false
        }
    } catch (PDOException $e) { //Cualquier error sql se captura dentro del catch 
        error_log("-> DEBUG LOGIN: Error SQL en loginUsuario: " . $e->getMessage());
        return [
            'status' => 'error',
            'mensaje' => 'error SQL'
        ];
    }
}

function casosPorTipo($pdo)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo");

    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($conteo && count($conteo) >= 0) {
            $nombres = [];
            $totales = [];

            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int) $temp['total'];    // 
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: " . $e->getMessage());
        return false;
    }
}

function casosPorTipoComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];

            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int) $temp['total'];    // 
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: " . $e->getMessage());
        return false;
    }
}

function casosPorTipoMesComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo_mes_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];

            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int) $temp['total'];    // 
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: " . $e->getMessage());
        return false;
    }
}

function casosPorTipoSemanaComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo_semana_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];

            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int) $temp['total'];    // 
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: " . $e->getMessage());
        return false;
    }
}

function casosPorTipoMes($pdo)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo_mes");

    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];

            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int) $temp['total'];    // 
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: " . $e->getMessage());
        return false;
    }
}

function casosPorTipoSemana($pdo)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo_semana");

    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];

            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int) $temp['total'];    // 
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: " . $e->getMessage());
        return false;
    }
}

function casosPorComisionado($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_comisionado");

    try {
        $stmt->execute();
        $casosComisionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosComisionados) {
            $comisionado = [];
            $total = [];

            foreach ($casosComisionados as $temp) {
                $comisionado[] = $temp['comisionado'];
                $total[] = (int) $temp['total_casos'];
            }

            return [
                'comisionado' => $comisionado,
                'casos' => $total
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("error en la obtención de casos por comisionado" . $e->getMessage());
        return false;
    }
}

function casosPorComisionadoMes($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_comi_mes");

    try {
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($datos) {
            $comisionado = [];
            $total = [];

            foreach ($datos as $temp) {
                $comisionado[] = $temp['comisionado'];
                $total[] = (int)$temp['total_casos'];
            }

            return [
                'comisionado' => $comisionado,
                'casos' => $total
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("error en la obtención de casos por comisionado" . $e->getMessage());
        return false;
    }
}

function casosPorComisionadoSemana($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_comi_semana");

    try {
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($datos) {
            $comisionado = [];
            $total = [];

            foreach ($datos as $temp) {
                $comisionado[] = $temp['comisionado'];
                $total[] = (int)$temp['total_casos'];
            }

            return [
                'comisionado' => $comisionado,
                'casos' => $total
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("error en la obtención de casos por comisionado" . $e->getMessage());
        return false;
    }
}

function casosPorMes($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_mes"); //Se llama al sp (storage procedure)

    try {
        $stmt->execute(); //Se ejecuta dentro de un try/catch
        $mesesCasos = $stmt->fetchAll(PDO::FETCH_ASSOC); //se almacenan los valores como un array asociativo dentro de la variable
        $stmt->closeCursor();

        if ($mesesCasos) { //Validamos el retorno de datos en la variable
            //Se declaran arrays vacios para evitar undefined variable
            $mes = [];
            $casos = [];

            foreach ($mesesCasos as $temp) { //Se recorren los arrays con la palaba reservada
                $mes[] = $temp['mes']; //Guardamos los valores de mes dentro de su variable
                $casos[] = (int) $temp['total_casos']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
            }

            return [
                'mes' => $mes,
                'casos' => $casos
            ]; //Retornamos en array asociativo con los datos corregidos
        } else {
            return false; //En caso de no retorno retornamos false
        }
    } catch (PDOException $e) { //Captura de errores sql e imprimirlos en el log del servidor
        error_log("Error en la obtencion de los datos por mes " . $e->getMessage());
        return false;
    }
}

function casosPorUnMes($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_un_mes"); //Se llama al sp (storage procedure)

    try {
        $stmt->execute(); //Se ejecuta dentro de un try/catch
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC); //se almacenan los valores como un array asociativo dentro de la variable
        $stmt->closeCursor();

        if ($datos) { //Validamos el retorno de datos en la variable
            //Se declaran arrays vacios para evitar undefined variable
            $dia = [];
            $casos = [];

            foreach ($datos as $temp) { //Se recorren los arrays con la palaba reservada
                $dia[] = $temp['dia']; //Guardamos los valores de mes dentro de su variable
                $casos[] = (int) $temp['total_casos']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
            }

            return [
                'dia' => $dia,
                'casos' => $casos
            ]; //Retornamos en array asociativo con los datos corregidos
        } else {
            return false; //En caso de no retorno retornamos false
        }
    } catch (PDOException $e) { //Captura de errores sql e imprimirlos en el log del servidor
        error_log("Error en la obtencion de los datos por mes " . $e->getMessage());
        return false;
    }
}

function casosPorSemana($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_semana"); //Se llama al sp (storage procedure)

    try {
        $stmt->execute(); //Se ejecuta dentro de un try/catch
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC); //se almacenan los valores como un array asociativo dentro de la variable
        $stmt->closeCursor();

        if ($datos) { //Validamos el retorno de datos en la variable
            //Se declaran arrays vacios para evitar undefined variable
            $dia = [];
            $casos = [];

            foreach ($datos as $temp) { //Se recorren los arrays con la palaba reservada
                $dia[] = (int)$temp['dia_semana'];
                $casos[] = (int) $temp['casos_dia']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
            }

            return [
                'dia' => $dia,
                'casos' => $casos
            ]; //Retornamos en array asociativo con los datos corregidos
        } else {
            return false; //En caso de no retorno retornamos false
        }
    } catch (PDOException $e) { //Captura de errores sql e imprimirlos en el log del servidor
        error_log("Error en la obtencion de los datos por mes " . $e->getMessage());
        return false;
    }
}

function casosPorEstado($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_estado");

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];

            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstado[0]['gran_total']
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: " . $e->getMessage());
        return false;
    }
}

function casosPorEstadoComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_estado_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];

            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstado[0]['gran_total']
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: " . $e->getMessage());
        return false;
    }
}

function casosPorEstadoMesComi($pdo, $documento) // Filtra por mes
{
    $stmt = $pdo->prepare("CALL sp_casos_por_estado_mes_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];

            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstado[0]['gran_total']
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: " . $e->getMessage());
        return false;
    }
}

function casosPorEstadoSemanaComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_estado_semana_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];

            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstado[0]['gran_total']
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: " . $e->getMessage());
        return false;
    }
}


function casosPorEstadoMes($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_estado_mes");

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];

            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstado[0]['gran_total']
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: " . $e->getMessage());
        return false;
    }
}

function casosPorEstadoSemana($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_estado_semana");

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];

            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            return [
                'estado' => $estados,
                'casos' => $casos,
                'total' => $casosEstado[0]['gran_total']
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: " . $e->getMessage());
        return false;
    }
}

function usuariosPorRol($pdo)
{
    try {
        $stmt = $pdo->prepare("CALL sp_reporte_usuarios_rol()");
        $stmt->execute();

        $usuariosRol = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $roles = [];
        $totales = [];
        $totalGeneral = 0;

        foreach ($usuariosRol as $temp) {
            $roles[] = $temp['rol'];
            $totales[] = $temp['total'];
            $totalGeneral += $temp['total'];
        }

        return [
            "rol" => $roles,
            "usuarios" => $totales,
            "total" => $totalGeneral
        ];
    } catch (PDOException $e) {
        return false;
    }
}

function usuariosPorEstado($pdo)
{
    try {
        $stmt = $pdo->prepare("CALL sp_reporte_usuarios_estado()");
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $estados = [];
        $totales = [];
        $totalGeneral = 0;

        foreach ($data as $row) {
            $estados[] = $row['estado'];
            $totales[] = $row['total'];
            $totalGeneral += $row['total'];
        }

        return [
            "estado" => $estados,
            "usuarios" => $totales,
            "total" => $totalGeneral
        ];
    } catch (PDOException $e) {
        return false;
    }
}


function casosPorProceso($pdo)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_proceso");

    try {
        $stmt->execute();
        $casosProceso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosProceso && count($casosProceso) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProceso as $temp) {
                $proceso[] = $temp['proceso'];           // ← Coincide con el SP
                $casos[] = (int) $temp['total_casos'];    // ← Coincide con el SP
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_proceso no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener los casos por proceso: " . $e->getMessage());
        return false;
    }
}


function casosPorProcesoComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_proceso_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $casosProceso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosProceso && count($casosProceso) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProceso as $temp) {
                $proceso[] = $temp['proceso'];           // ← Coincide con el SP
                $casos[] = (int) $temp['total_casos'];    // ← Coincide con el SP
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_proceso no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener los casos por proceso: " . $e->getMessage());
        return false;
    }
}

function casosPorProcesoMesComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_proceso_mes_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $casosProceso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosProceso && count($casosProceso) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProceso as $temp) {
                $proceso[] = $temp['proceso'];           // ← Coincide con el SP
                $casos[] = (int) $temp['total_casos'];    // ← Coincide con el SP
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_proceso no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener los casos por proceso: " . $e->getMessage());
        return false;
    }
}

function casosPorProcesoSemanaComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_casos_por_proceso_semana_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $casosProceso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosProceso && count($casosProceso) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProceso as $temp) {
                $proceso[] = $temp['proceso'];           // ← Coincide con el SP
                $casos[] = (int) $temp['total_casos'];    // ← Coincide con el SP
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_proceso no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener los casos por proceso: " . $e->getMessage());
        return false;
    }
}

function traerCaso($pdo, $idCaso)
{
    $stmt = $pdo->prepare("CALL sp_obtener_caso_por_id(?)");
    $stmt->bindParam(1, $idCaso, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $casoGestionar = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casoGestionar) {
            return [
                'status' => 'ok',
                'data' => $casoGestionar
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener el caso solicitado" . $e->getMessage());
        return false;
    }
}



function listarUsuarios($pdo)
{
    $stmt = $pdo->prepare("CALL sp_listar_usuarios()");

    try {
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($usuarios) {
            return [
                'status' => 'ok',
                'data' => $usuarios
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener usuarios " . $e->getMessage());
        return false;
    }
}

function gestionarUsuario($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_traer_usuario(?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $usuarioGestionar = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($usuarioGestionar) {
            return [
                'status' => 'ok',
                'data' => $usuarioGestionar
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener el caso solicitado " . $e->getMessage());
        return false;
    }
}

//Funciona para que podamos obtener el resumen productivo de los comisionados

function obtenerResumenProductividad($pdo)
{
    try {
        $stmt = $pdo->prepare("CALL sp_resumen_productividad_comisionados()");
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        return $resultados;
    } catch (PDOException $e) {
        error_log("Error al obtener resumen de comisionados: " . $e->getMessage());
        return false;
    }
}

//Funciona para que nos traiga los casos que los comisionados han hecho en el mes
function obtenerCaracterizacionUsuarios($pdo)
{
    try {
        $stmt = $pdo->prepare("CALL sp_caracterizacion_usuarios()");
        $stmt->execute;

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor;

        return $resultados;
    } catch (PDOException $e) {
        error_log("Error al obtener casos de los usuarios: " . $e->getMessage());
        return false;
    }
}

//Funciona para mostrarnos un análasis de los tipos de PQRS del sistema

function obtenerAnalisisDemanda($pdo)
{
    try {
        $stmt = $pdo->prepare("CALL sp_analisis_demanda()");
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $resultados;
    } catch (PDOException $e) {
        error_log("Error en obtener el analisis de las demandas: " . $e->getMessage());
        return false;
    }
}

function listarProceso($pdo)
{
    // 1. Preparamos la llamada al procedimiento
    $stmt = $pdo->prepare("CALL sp_listar_proceso_organizacional()");

    try {
        // 2. Ejecutar la sentencia
        $stmt->execute();

        // 3. Traemos TODAS las filas
        $procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Limpiamos el cursor para permitir futuras consultas en la misma conexión
        $stmt->closeCursor();

        // 4. Verificamos si hay datos
        if ($procesos !== false) {
            return [
                'status' => 'ok',
                'data' => $procesos // Retorna el array de objetos
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'No se pudieron recuperar los datos'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error en listarProceso: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Error interno del servidor'
        ];
    }
}

function tablaBaseExcel($pdo)
{
    $stmt = $pdo->prepare("CALL sp_reporte_pqrs_excel()");

    try {
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        if ($data !== false) {
            return $data;
        } else {
            return false;
        }
    } catch (PDOException) {
    }
}

function listarCasosComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_listar_caso_por_comisionado(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $listarCasosComi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($listarCasosComi) {
            return [
                'status' => 'ok',
                'data' => $listarCasosComi
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al listar caso " . $e->getMessage());
        return false;
    }
}

function listarProcesosActivos($pdo)
{
    $stmt = $pdo->prepare("CALL sp_listar_procesos_activos()");

    try {
        $stmt->execute();
        $procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($procesos) {
            return [
                'status' => 'ok',
                'data' => $procesos
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al listar procesos activos: " . $e->getMessage());
        return false;
    }
}

function listarTiposCaso($pdo)
{
    $stmt = $pdo->prepare("CALL sp_listar_tipos_caso()");

    try {
        $stmt->execute();
        $tiposCaso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($tiposCaso) {
            return [
                'status' => 'ok',
                'data' => $tiposCaso
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al listar tipos de caso: " . $e->getMessage());
        return false;
    }
}

function listarEstadosCaso($pdo)
{
    $stmt = $pdo->prepare("CALL sp_listar_estados_caso()");

    try {
        $stmt->execute();
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($estados) {
            return [
                'status' => 'ok',
                'data' => $estados
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al listar estados de caso: " . $e->getMessage());
        return false;
    }
}

function validarEstado($pdo, $idCaso)
{
    $stmt = $pdo->prepare("CALL sp_validacion_estado_caso(?)");

    $stmt->bindParam(1, $idCaso, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $estado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($estado) {
            return [
                'status' => 'ok',
                'data' => $estado
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al listar estados de caso: " . $e->getMessage());
        return false;
    }
}
function gestionarProceso($pdo, $nombre)
{

    $stmt = $pdo->prepare("CALL sp_traer_proceso(?)");
    $stmt->bindParam(1, $nombre, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $procesoGestionar = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($procesoGestionar) {
            return [
                'status' => 'ok',
                'data' => $procesoGestionar
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener el proceso solicitado " . $e->getMessage());
        return false;
    }
};

function conteoGeneral($pdo)
{
    $stmt = $pdo->prepare("CALL sp_resumen_casos_global");

    try {
        $stmt->execute();
        $conteoGeneral = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$conteoGeneral) {
            return false;
        }

        return $conteoGeneral;
    } catch (PDOException $e) {

        error_log("Error al obtener el conteo general " . $e->getMessage());
        return false;
    }
}

function conteoPorUsuario($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_resumen_casos_por_documento(?)");

    try {
        $stmt->bindParam(1, $documento, PDO::PARAM_STR);
        $stmt->execute();
        $conteoPorUsuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$conteoPorUsuario) {
            return false;
        }

        return $conteoPorUsuario;
    } catch (PDOException $e) {

        error_log("Error al obtener el conteo por usuario " . $e->getMessage());
        return false;
    }
}

function listarNotiAdmin($pdo)
{
    $stmt = $pdo->prepare("CALL sp_listar_noti_admin");
    try {
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($data) {
            $dataFormateada = [];
            
            foreach ($data as $temp) {
               $dataFormateada[] = [
                'id' => $temp['id_notificacion'],
                'documento' => $temp['documento'],
                'descripción' =>  $temp['mensaje'],
                'fecha' => $temp['fecha']
               ];
            }

            return [
                'status' => 'ok',
                'data' => $dataFormateada
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {

        error_log("Error al obtener notificaciones " . $e->getMessage());
        return false;
    }
}

function listarNotiComi($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_listar_noti_comi(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($data) {
            $dataFormateada = [];
            
            foreach ($data as $temp) {
               $dataFormateada[] = [
                'id' => $temp['id_notificacion'],
                'documento' => $temp['documento'],
                'descripción' =>  $temp['mensaje'],
                'fecha' => $temp['fecha']
               ];
            }

            return [
                'status' => 'ok',
                'data' => $dataFormateada
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {

        error_log("Error al obtener notificaciones " . $e->getMessage());
        return false;
    }
}
