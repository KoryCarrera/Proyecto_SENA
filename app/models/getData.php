<?php
require_once '../config/conexion.php';

function listarCasos($pdo)
{

    $stmt = $pdo->prepare("CALL sp_listar_casos()");


    try {
        $stmt->execute();
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $casos;
    } catch (PDOException $e) {
        error_log('Error al obtener los casos: ' . $e->getMessage());
        return null;
    }
}

function casosPorComisionado($pdo, $document)
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
    $stmt = $pdo->prepare("CALL sp_buscar_usuario(:documento, :nombre)");
    $stmt->bindParam(':documento', $document, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $name, PDO::PARAM_STR);


    try {
        $stmt->execute();
        $usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $usuario;
    } catch (PDOException $e) {
        error_log("Error al buscar el usuario: " . $e->getMessage());
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
    } catch (PDOException $e) {
        error_log("Error al obtener el resumen general: " . $e->getMessage());
        return null;
    }
}

function loginUsuario($pdo, $documento, $contraseña)
{
    $stmt = $pdo->prepare("CALL sp_login_usuario(:documento, :contraseña)");
    $stmt->bindParam(':documento', $documento, PDO::PARAM_INT);
    $stmt->bindParam(':contraseña', $documento, PDO::PARAM_STR);

    try{
        $stmt->execute();
        $stmt->Closecursor();
    } catch(PDOException){
        return false;
    }
}