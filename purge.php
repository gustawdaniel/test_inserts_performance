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

$n=1;

$generator = new SchemaGenerator($n);
$generator->apply($conn); // rebuild database and clear it
$conn->delete('log',[1=>1]);
$conn->delete('machine',[1=>1]);
$conn->delete('major_1',[1=>1]);
$conn->delete('major_2',[1=>1]);
$conn->delete('minor_1',[1=>1]);
