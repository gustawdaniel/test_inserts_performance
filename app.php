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

$N = 63;
$L = 50;
$K = 50;

$progress = new CustomProgressManager(0, $N*$K*$L, 106, '=', ' ', '>');
$progress->getRegistry()->setValue("state", "Progress");

//$cc=0;

for($n=1;$n<=$N;$n++){ // number of minor tables in test

    $generator = new SchemaGenerator($n);
    $generator->apply($conn); // rebuild database and clear it

    for($l=1;$l<=$L;$l++){ // number of rows minor table

        for($i=1;$i<=$n;$i++){ // to any minor table
            $conn->insert('minor_'.$i, array('id' => $l)); // append one row with current key
        }

        for($k=1;$k<=$K;$k++){//number of rows in major table
            $conn->delete("major_1",[1=>1]);
            $progress->getRegistry()->setValue("state", "N=$n|L=$l|K=$k");

            $conn->beginTransaction();
            try{
                $t1=microtime(true);
                for($i=1;$i<=$k;$i++){                // row in table major
                    $content = ['id'=>$i];
                    for($j=1;$j<=$n;$j++){            // foreign key of row
                        $content['minor_'.$j.'_id'] = rand(1,$l);
                    }
                    $conn->insert('major_1', $content);
                }
                $conn->commit();
                $t2=microtime(true);
                $logger->log($n,$l,$k,$t2-$t1,"1","workstation",$conn);
//                $conn->insert('major_1', array(
//                    "n"=>$n,
//                    "l"=>$l,
//                    "k"=>$k,
//                    "t"=>$t2-$t1,
//                    "v"=>"1",
//                    "message"=>"ins"
//                ));

                $progress->advance();

            } catch(\Exception $e) {
               $conn->rollBack();
                throw $e;
            }
        }
    }
}

