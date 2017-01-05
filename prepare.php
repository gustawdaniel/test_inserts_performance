<?php

require_once "vendor/autoload.php";
require_once 'lib/model/SchemaGenerator.php';
require_once 'lib/util/CustomProgressManager.php';
require_once 'lib/util/Logger.php';
use Doctrine\DBAL\DriverManager;
use Model\SchemaGenerator;
use Util\CustomProgressManager;
use Util\Logger;


$connectionParams = array(
    'dbname' => 'training',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);
$conn = DriverManager::getConnection($connectionParams);
$logger = new Logger();
//$N = 5;
//$L = 1;
//$K = 5;

$scale = 1e4;
$N = 10;
$L = 1*$scale;
$K = 100*$scale;

$progress = new CustomProgressManager(0, ($K+$L*$N)/$scale, 106, '=', ' ', '>');
$progress->getRegistry()->setValue("state", "Progress");

$generator = new SchemaGenerator($N);
$generator->apply($conn); // rebuild database and clear it

for($n=1;$n<=$N;$n++) { // number of minor tables in test
    $conn->beginTransaction();
    try {
        for ($l = 1; $l <= $L; $l++) {
            $conn->insert('minor_' . $n, array('id' => $l));
            $l%$scale || $progress->advance();
        }
        $conn->commit();
    } catch(\Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

$conn->beginTransaction();
try {
    for($k=1;$k<=$K;$k++){                // row in table major
        $content = ['id'=>$k];
        for($n=1;$n<=$N;$n++){            // foreign key of row
            $content['minor_'.$n.'_id'] = rand(1,$L);
        }
        $conn->insert('major_1', $content);
        $k%$scale || $progress->advance();
    }
    $conn->commit();
} catch(\Exception $e) {
    $conn->rollBack();
    throw $e;
}