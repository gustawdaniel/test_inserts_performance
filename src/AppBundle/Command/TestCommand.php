<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 16:24
 */

namespace AppBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\ProgressBar;

class TestCommand extends Base
{

    private $n,$l,$name,$k,$noLog;

    protected function configure()
    {
        $this->setName('app:test')
            ->addArgument('name',InputArgument::REQUIRED, 'Name of test')
            ->addArgument('N')
            ->addArgument('L')
            ->addArgument('K')
            ->addOption('no-log');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Starting test...');

        $progress = new ProgressBar($output,$this->n*$this->l*$this->k);
        $progress->setFormat('very_verbose');
        $progress->start();

        if($this->name=="first"){


            for($n=1;$n<=$this->n;$n++)  // base shape, n - number of minors
            {
                // clear all data and rebuild schema
                $this->executeWithLog('fixtures:load -q major '.$n.' 0 0',$n,0,0,'clear_major_before_reshape'); // deleting
                $this->executeWithLog('schema:update -fq '.$n,$n,0,0,'reshape'); // reshaping
                $this->executeWithLog('fixtures:load -q minor '.$n.' 0',$n,0,0,'clear_minor_before_reshape'); // deleting

                for($l=1;$l<=$this->l;$l++) // size of minor, l - rows in minor
                {
                    // clear main, and append one to minor
                    $this->executeWithLog('fixtures:load -q major '.$n.' '. 0 . ' ' . 0,$n,$l,0,'clear_major_before_minor_increment');
                    $this->executeWithLog('fixtures:load -q minor '.$n.' '. 1 .' --append',$n,$l,0,'append_one_to_minor');

                    for($k=1;$k<=$this->k;$k++) // size of major, k - rows in major
                    {
                        // clear main and rebuild it from zero, to measurement
                        $this->executeWithLog('fixtures:load -q major '.$n.' 0 0',$n,$l,$k,'clear_major_before_main');
                        $this->executeWithLog('fixtures:load -q major '.$n.' '.$l.' '.$k,$n,$l,$k,'insert_major');
                        $progress->advance();
                    }
                }
            }
        }

        $progress->finish();
        $output->writeln(sprintf("\n".'Test finished successfully! "<info>%s</info>" operations executed', $this->n*$this->l*$this->k));
    }

    private function executeWithLog($command,$n,$l,$k,$appendedName)
    {
        $t1 = microtime(true);
        exec('php bin/console app:'.$command); // insert to empty table
        $t2 = microtime(true);
        if(!$this->noLog){ $this->log($n,$l,1,$k,$t2-$t1,$this->getName().'_'.$this->name.'_'.$appendedName); }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->n = $input->getArgument('N') ?: $this->getContainer()->getParameter('N');
        $this->l = $input->getArgument('L') ?: $this->getContainer()->getParameter('L');
        $this->k = gettype($input->getArgument('K')) != "NULL" ? $input->getArgument('K') : $this->getContainer()->getParameter('K');
        $this->name = $input->getArgument('name');
        $this->noLog = $input->getOption('no-log');
    }

    private function log($n,$l,$k0,$k,$time,$name)
    {
        $st = $this->conn->executeQuery(
            "INSERT INTO log (n,l,k0,k,execution_time,operation,v) VALUES (?,?,?,?,?,?,?)",
            [$n,$l,$k0,$k,$time,$name,2]
        );
    }
}