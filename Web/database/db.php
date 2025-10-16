<?php
$host = 'localhost';
$db   = 'ibanking';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8;";
$dbusername = "root";
$dbpassword = "";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword, $options);
} catch (\PDOException $e) {
    exit('Connection failed: ' . $e->getMessage());
}
?>