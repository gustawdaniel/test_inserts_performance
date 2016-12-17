<?php

require_once "vendor/autoload.php";
require_once 'model/SchemaGenerator.php';
use Doctrine\DBAL\DriverManager;
use Model\SchemaGenerator;
use Doctrine\DBAL\Schema\Comparator;
use Symfony\Component\Console\Helper\ProgressBar;

$connectionParams = array(
    'dbname' => 'training',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);
$conn = DriverManager::getConnection($connectionParams);


//$sql = "SELECT a FROM o";
//$stmt = $conn->query($sql); // Simple, but has several drawbacks
//
//while ($row = $stmt->fetch()) {
//    echo $row['a'];
//}


$schema = (new SchemaGenerator(10))->generate();
$queries = (new Comparator())->compare($conn->getSchemaManager()->createSchema(), $schema)->toSql($conn->getDatabasePlatform());


//        $progress = new ProgressBar($output, count($queries));
//        $progress->setFormat('very_verbose');
//        $progress->start();
    foreach($queries as $key => $query) {
        $conn->prepare($query)->execute();
//        $progress->advance();
        progressBar($key,count($queries));
    }
//        $progress->finish();


function progressBar($done, $total) {
    $perc = floor(($done / $total) * 100);
    $left = 100 - $perc;
    $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    fwrite(STDERR, $write);
}