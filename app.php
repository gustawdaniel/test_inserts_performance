<?php

require_once "vendor/autoload.php";
use Doctrine\DBAL\DriverManager;

$connectionParams = array(
    'dbname' => 'training',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);
$conn = DriverManager::getConnection($connectionParams);


$sql = "SELECT a FROM o";
$stmt = $conn->query($sql); // Simple, but has several drawbacks

while ($row = $stmt->fetch()) {
    echo $row['a'];
}