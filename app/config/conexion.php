<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    // --- TEST TEMPORAL: Borra esto cuando funcione ---
    // var_dump($_ENV['DB_NAME']); 
    // ------------------------------------------------

    $host = 'db_sena'; 
    $dbname = $_ENV['DB_NAME'] ?? 'proyectosena_sb';
    $user = $_ENV['USER_DB'] ?? 'root';
    $pass = $_ENV['DB_ROOT_PASSWORD'] ?? 'root';

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