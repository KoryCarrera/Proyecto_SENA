<?php
require_once '../config/conexion.php';

function listarCasos($pdo)
{

    $stmt = $pdo->prepare("CALL sp_listar_casos()");


    try {
        $stmt->execute();
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if($casos) {
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

    $stmt = $pdo->prepare("CALL sp_listar_seguimientos_por_caso(:id)");
    $stmt->bindParam(':id', $idCaso, PDO::PARAM_INT);

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

function buscarUsuario($pdo, $document, $name)
{   
    //Se prepara la sentencia sql a ejecutar
    $stmt = $pdo->prepare("CALL sp_buscar_usuario(:documento, :nombre)");
    //Se asignan los parametros necesarios para el sp
    $stmt->bindParam(':documento', $document, PDO::PARAM_STR);
    $stmt->bindParam(':nombre', $name, PDO::PARAM_STR);

    //Ejecutamos dentro de un try/catch para manejo de errores
    try {
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC); //Fetch debido a que es un unico registro para hacerlo un objeto y no un array
        $stmt->closeCursor(); //Cerramos el "tunel" con la Bases de datos para evitar errore y sobre cargas
        return $usuario;
    } catch (PDOException $e) {
        error_log("Error al buscar el usuario: " . $e->getMessage()); //Captamos errores en el log del servidor 
        return null;
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
        }else{
            return [
                'status' => 'error',
                'mensaje' => 'Credenciales inválidas'
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
        
        if($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];
            
            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // 
                $totales[] = (int)$temp['total'];    // 
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
        error_log("Error SQL en casosPorTipo: ". $e->getMessage());
        return false;
    }
}

function casosPorComisionado($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_comisionado");

    try {
        $stmt->execute();
        $casosComisionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosComisionados) {
            $comisionado = [];
            $total = [];

            foreach($casosComisionados as $temp) {
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
        error_log("error en la obtención de casos por comisionado". $e->getMessage());
        return false;
    }
}

function casosPorMes ($pdo) {
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
                $casos[] = (int)$temp['total_casos']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
            }

            return [
                'mes' => $mes,
                'casos' => $casos
            ]; //Retornamos en array asociativo con los datos corregidos
        } else {
            return false; //En caso de no retorno retornamos false
        }
    } catch (PDOException $e) { //Captura de errores sql e imprimirlos en el log del servidor
        error_log("Error en la obtencion de los datos por mes ". $e->getMessage());
        return false;
    }
}

function casosPorEstado($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_estado");

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if($casosEstado && count($casosEstado) > 0) { //Este if valida que casosEstados no sea false y sus registros sean mayor a 0
            $estados = [];
            $casos = [];
            
            foreach ($casosEstado as $temp) { //Palabra reservada para recorrer arrays
                $estados[] = $temp['nombre_estado']; 
                $casos[] = (int)$temp['total_casos'];
            }
            
            return [
                'estado' => $estados,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }

    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: ". $e->getMessage());
        return false;
    }
}


function casosPorProceso($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_proceso");

    try {
        $stmt->execute();
        $casosProceso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if($casosProceso && count($casosProceso) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProceso as $temp) {
                $proceso[] = $temp['proceso'];           // ← Coincide con el SP
                $casos[] = (int)$temp['total_casos'];    // ← Coincide con el SP
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
        error_log("Error al obtener los casos por proceso: ". $e->getMessage());
        return false;
    }
}

function traerCaso($pdo, $idCaso) {
    $stmt = $pdo->prepare("CALL sp_obtener_caso_por_id(?)");
    $stmt->bindParam(1, $idCaso, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $casoGestionar = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if($casoGestionar) {
            return [
                'status' => 'ok',
                'data' => $casoGestionar
            ];
        } else {
            return false;
        } 
    } catch (PDOException $e) {
        error_log("Error al obtener el caso solicitado". $e->getMessage());
        return false;
    }
}

function listarUsuarios($pdo){
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
        error_log("Error al obtener usuarios ". $e->getMessage());
        return false;
    }
}

function gestionarUsuario($pdo, $documento){
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
        error_log("Error al obtener el caso solicitado ". $e->getMessage());
        return false;
    }
}