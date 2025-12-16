<?php 

//Se declaran las variables de certificación para la bd

$host = 'localhost';
$port = '3306';
$dbname = 'proyecto_senadb';
$user = 'root';
$pass = '';

//hola Kory como estas?

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO ($dsn, $user, $pass);
    //echo 'Conexion exitosa a la base de datos';
} catch (PDOException $e) {
    //echo 'Conexion fallida: ' . $e->getMessage(); //hola
}

?>
