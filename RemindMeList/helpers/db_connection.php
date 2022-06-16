<?php
// Database connection php only file to load into each page where a DB connection is required
$host = '127.0.0.1';
$db   = 'reminderlist';
$db_user = 'bit_academy';
$db_pass = 'bit_academy';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

# Options from MYSQL tutorial
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    $err = "Database went boom";
} 