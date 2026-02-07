<?php 

//Se declaran las variables de certificación para la bd

$host = 'db_sena';
$port = '3306';
$dbname = 'proyectosena_db';
$user = 'root';
$pass = 'root';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO ($dsn, $user, $pass);
    //echo 'Conexion exitosa a la base de datos';
} catch (PDOException $e) {
    //echo 'Conexion fallida: ' . $e->getMessage();
}

?>
