<?php

require_once "vendor/autoload.php";
require_once 'lib/model/SchemaGenerator.php';
require_once 'lib/util/CustomProgressManager.php';
use Doctrine\DBAL\DriverManager;
use Model\SchemaGenerator;
use Util\CustomProgressManager;
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

//$N = 2; $L = 10; $K = 5; $lStep=10;// option for test
$N = 63; $L = 50; $K = 50; $lStep=1000;

$progress = new CustomProgressManager(0, $N*$K*$L, 106, '=', ' ', '>');
$progress->getRegistry()->setValue("state", "Progress");

$doTestStmt=$conn->prepare("CALL do_test(?,?,?,?)");



for($n=1;$n<=$N;$n++) { // number of minor tables in test

    $generator = new SchemaGenerator($n);
    $generator->apply($conn); // rebuild database and clear it

    for ($l = 1; $l <= $L; $l++) { // number of rows minor table

        for ($i = 1; $i <= $n; $i++) { // to any minor table
            for($j=1;$j<=$lStep;$j++) {
                $conn->insert('minor_' . $i, array('id' => null)); // append one row with current key
            }
        }

        for ($k = 1; $k <= $K; $k++) {//number of rows in major table
            $progress->getRegistry()->setValue("state", "N=$n|L=$l|K=$k");

            $t1 = microtime(true);
            $doTestStmt->bindValue(1,$n);
            $doTestStmt->bindValue(2,$l*$lStep);
            $doTestStmt->bindValue(3,$k);
            $doTestStmt->bindValue(4,$config["guid"]);
            $doTestStmt->execute();
            $doTestStmt->closeCursor();
            $t2 = microtime(true);

            $conn->insert('log', array("n"=>$n, "l"=>$l*$lStep, "k"=>$k, "t"=>$t2-$t1, "machine_id"=>$config["guid"], "message"=>"app"));
            $progress->advance();
        }
    }
}
