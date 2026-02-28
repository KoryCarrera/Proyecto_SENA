<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try {

    $host = 'db_sena'; 
    $dbname = getenv('DB_NAME') ?? 'proyectosena_sb';
    $user = getenv('USER_DB') ?? 'root';
    $pass = getenv('DB_ROOT_PASSWORD') ?? 'root';

    $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (Exception $e) {
    error_log("DETALLE ERROR PDO: " . $e->getMessage());
    die("Error crítico de conexión: " . $e->getMessage());
}