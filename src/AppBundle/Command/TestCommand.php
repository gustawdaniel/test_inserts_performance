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


            for($n=1;$n<=$this->n;$n++)
            {
                exec('php bin/console app:fixtures:load -q major '.$n.' '. 0 . ' ' . 0); // deleting
                exec('php bin/console app:schema:update -fq '.$n); // rebuild schema
                for($l=1;$l<=$this->l;$l++)
                {
                    exec('php bin/console app:fixtures:load -q major '.$n.' '. 0 . ' ' . 0); // deleting
                    exec('php bin/console app:fixtures:load -q minor '.$n.' '. 1 .' --append');
                    for($k=1;$k<=$this->k;$k++)
                    {
                        exec('php bin/console app:fixtures:load -q major '.$n.' '. 0 . ' ' . 0); // deleting
                        $t1 = microtime(true);
                        exec('php bin/console app:fixtures:load -q major '.$n.' '. $l . ' ' . $k); // insert to empty table
                        $t2 = microtime(true);

                        if(!$this->noLog){
                            $this->log($n,$l,1,$k,$t2-$t1,$this->getName().'_'.$this->name);
                        }
                        $progress->advance();
                    }
                }
            }
        }

        $progress->finish();
        $output->writeln(sprintf("\n".'Test finished successfully! "<info>%s</info>" operations executed', $this->n*$this->l*$this->k));
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
            "INSERT INTO log (n,l,k0,k,execution_time,operation) VALUES (?,?,?,?,?,?)",
            [$n,$l,$k0,$k,$time,$name]
        );
    }
}