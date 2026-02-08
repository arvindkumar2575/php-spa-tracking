<?php
require_once __DIR__ . '/env.php';
date_default_timezone_set('Asia/Kolkata');
$visitor_table = "test_visitors";


$host = $_ENV['DB_HOSTNAME'];
$db = $_ENV['DB_NAME'];
$user   = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed");
}
