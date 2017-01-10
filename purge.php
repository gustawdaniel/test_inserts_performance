<?php
require_once "vendor/autoload.php";
require_once 'lib/model/SchemaGenerator.php';
use Doctrine\DBAL\DriverManager;
use Model\SchemaGenerator;
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents(__DIR__.'/config/parameters.yml'))["parameters"];
$connectionParams = array(
    'dbname' => $config["dbname"],
    'user' => $config["user"],
    'password' => $config["password"],
    'host' => $config["host"],
    'driver' => $config["driver"],
);
$conn = DriverManager::getConnection($connectionParams);

$n=0;

$generator = new SchemaGenerator($n);
$generator->apply($conn); // rebuild database and clear it