<?php
require_once 'vendor/autoload.php';
require_once 'util/CustomProgressManager.php';
//use ProgressBar\Manager;
use Util\CustomProgressManager;

$progressBar = new CustomProgressManager(0, 101, 200, '=', ' ', '>');

$state = "Progress";


//$progressBar->setFormat('%state% : %current%/%max% [%bar%] %percent%% (%elaps%/%total%) ETA: %eta%');


$progressBar->getRegistry()->setValue("state",$state);

$t1 = microtime(true);
for ($i = 0; $i <= 100; $i++)
{
    $progressBar->advance();
    usleep(100000);
    if($i%2){
        $progressBar->getRegistry()->setValue("state",$state);
        $state = "Regress ";
    } else {
        $progressBar->getRegistry()->setValue("state","Progress");
    }
}
$t2=microtime(true);

var_dump($progressBar->getRegistry()->getValue("advancement"));

//var_dump($t2-$t1);