<?php
require_once 'vendor/autoload.php';
require_once 'util/CustomProgressManager.php';
use Util\CustomProgressManager;

$progressBar = new CustomProgressManager(0, 101, 200, '=', ' ', '>');
$progressBar->getRegistry()->setValue("state", "Progress");

for ($i = 0; $i <= 100; $i++)
{
    $progressBar->advance();
    usleep(100000);
}