<?php 

//Se declaran las variables de certificación para la bd

$host = 'db_sena';
$port = '3306';
$dbname = getenv('DB_NAME');
$user = getenv('USER_DB');
$pass = getenv('DB_ROOT_PASSWORD');

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO ($dsn, $user, $pass);
    //echo 'Conexion exitosa a la base de datos';
} catch (PDOException $e) {
    //echo 'Conexion fallida: ' . $e->getMessage();
}

?>
