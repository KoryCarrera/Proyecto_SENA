<?php
class baseHelper {

public function __construct(protected  $conexion) 
{}
    

    public function consultObjectHelper($sp) {

        if (!$sp) {
            throw new Exception("datos ingresados al metodo vacios");
        }
        if (!is_string($sp)) {
            throw new Exception("El tipo de dato ingresado al metodo no es valido");
        }

        $stmt = $this->conexion->prepare("CALL $sp");

        try {
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data && is_array($data)){

                return $data;
            } else {
                throw new Exception("No se han podido obtener datos");
            }

        } catch (PDOException $e) {
            error_log('Ha ocurrido un error sql: ' . $e->getMessage());

            throw new Exception("Ha ocurrido un error, revisa los logs del servidor");
        }
    }

    public function consultSimpleHelper($sp){

        if (!$sp) {
            throw new Exception("Datos ingresados al metodos vacios");
        };

        if (!is_string($sp)) {
            throw new Exception("El tipo de dato ingresado al metodo es no valido");
        }
        $stmt = $this->conexion->prepare("CALL $sp");

        try{

            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data && is_array($data)){
                return $data;
            } else {
                throw new Exception("No se ha podido obtener datos de la consulta");
            }

        } catch (PDOException $e) {
            error_log("Ha ocurrido un error sql: " . $e->getMessage());

            throw new Exception("Ha ocurrido un error, revisa los logs del servidor");
        }
    }

    public function consultSimpleWithParams($sp, $params){
        
        if (!$sp || !$params) {
            throw new Exception("Datos ingresados al metodo vacios");
        }

        if (!is_array($params) || !is_string($sp)){
            throw new Exception("Datos ingresados al metodo invalidos, por favor verifica");
        };

        $stmt = $this->conexion->prepare("CALL $sp");
        
        $i = 1;
        foreach ($params as $param) {
            $stmt->bindValue($i, $param['value'], $param['type']);
            $i++;
        };

        try{

            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data && is_array($data)){
                return $data;
            } else {
                throw new Exception("No se ha podido obtener datos de la consulta con parametros");
            }

        } catch (PDOException $e) {
            error_log("Ha ocurrido un error sql: " . $e->getMessage());

            throw new Exception("Ha ocurrido un error, revisa los logs del servidor");
        };
    }

    public function consultObjectWithParams($sp, $params){
        
        if (!$sp || !$params) {
            throw new Exception("Datos ingresados al metodo vacios");
        }

        if (!is_array($params) || !is_string($sp)){
            throw new Exception("Datos ingresados al metodo invalidos, por favor verifica");
        };

        $stmt = $this->conexion->prepare("CALL $sp");
        
        $i = 1;
        foreach ($params as $param) {
            $stmt->bindValue($i, $param['value'], $param['type']);
            $i++;
        };

        try{
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data && is_array($data)){
                return $data;
            } else {
                throw new Exception("No se ha podido obtener datos de la consulta con parametros");
            }

        } catch (PDOException $e) {
            error_log("Ha ocurrido un error sql: " . $e->getMessage());

            throw new Exception("Ha ocurrido un error, revisa los logs del servidor");
        };
    }

    public function insertOrUpdateData($sp, $data){

        if (!$sp || !$data) {
            throw new Exception("Datos ingresados al metodo vacios");
        }
        if (!is_string($sp) || !is_array($data)){
            throw new Exception("El tipo de datos ingresados al metodo no son validos");
        }

        $stmt = $this->conexion->prepare("CALL $sp");

        $i = 1;
        foreach ($data as $param) {
            $stmt->bindValue($i, $param['value'], $param['type']);
            $i++;
        };

        try {
            $stmt->execute();
            $dataR = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return true;
        } catch (PDOException $e) {
            error_log("Ha ocurrido un erro sql: ". $e->getMessage());
            return false;
        }
    }
};