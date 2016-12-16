<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 02:29
 */

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\ProgressBar;

class FixtureCommand extends ContainerAwareCommand
{
    /**
     * @var Connection
     */
    private $conn;
    private $n,$l,$transaction,$append,$table,$k0,$k,$main;

    protected function configure()
    {
        $this->setName('app:fixture:load')
            ->addArgument('table',InputArgument::REQUIRED, 'Name of table')
            ->addArgument('N',InputArgument::OPTIONAL)
            ->addArgument('L')
            ->addArgument('K')
            ->addArgument('main')
            ->addOption('append')
            ->addOption('no-transaction');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch ($this->table) {
            case 'minor':
                $progress = new ProgressBar($output, $this->n*$this->l);
                $progress->setFormat('very_verbose');
                $progress->start();
                $progress->setRedrawFrequency(25);

                for($i=1;$i<=$this->n;$i++){
                    $this->conn->delete('minor_'.$i,[1=>1]);
                    for($j=1;$j<=$this->l;$j++){
                        $this->conn->insert('minor_'.$i, array('id' => $j));
                        $progress->advance();
                    }
                }

                $progress->finish();
                $output->writeln("\n".'Created <info>'.$this->l.'</info> rows in <info>'.$this->n.'</info> tables. In sum <info>'.$this->l*$this->n.'</info> inserts.');
                break;
            case 'major':
                if(!$this->append) { $this->conn->delete('main_'.$this->main,[1=>1]); }
                if($this->transaction) {$this->conn->beginTransaction();}
                try{
                    $progress = new ProgressBar($output, $this->k-$this->k0+1);
                    $progress->setFormat('very_verbose');
                    $progress->start();
                    $progress->setRedrawFrequency(200);

                    for($i=$this->k0;$i<=$this->k;$i++){                // row in table main
                        $content = ['id'=>$i];
                        for($j=1;$j<=$this->n;$j++){            // foreign key of row
                            $content['minor_'.$j.'_id'] = rand(1,$this->l);
                        }
                        $this->conn->insert('main_'.$this->main, $content);
                        $progress->advance();
                    }

                    $progress->finish();
                    $output->writeln("\n".'Created <info>' . ($this->k-$this->k0+1) . '</info> rows (<info>' . ($this->k0).'->'.($this->k) . '</info>) in table <info>main_'.$this->main.'</info>.');


                    if($this->transaction) {$this->conn->commit();}
                } catch(\Exception $e) {
                    if($this->transaction) {$this->conn->rollBack();}
                    throw $e;
                }
//            }
        break;
            default:
                $output->writeln('Please chose table:');
                $output->writeln(sprintf('    <info>%s (main|minor)</info> to execute the command', $this->getName()));
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->conn = $this->getContainer()->get('doctrine')->getConnection();
        $this->n = $input->getArgument('N') ?: $this->getContainer()->getParameter('N');
        $this->l = $input->getArgument('L') ?: $this->getContainer()->getParameter('L');
        $this->k = $input->getArgument('K') ?: $this->getContainer()->getParameter('K');
        $this->main = $input->getArgument('main') ?: 1;
        $this->table = $input->getArgument('table');
        $this->transaction = !$input->getOption('no-transaction');
        if($input->getOption('append')){
            $this->append = true;
            if($this->table == 'minor'){
                $this->k0 = $this->conn->fetchColumn('SELECT COUNT(*) FROM minor_1')+1;
                $this->l += $this->k0-1;
            } elseif($this->table == 'major'){
                $this->k0 = $this->conn->fetchColumn("SELECT COUNT(*) FROM main_".$this->main)+1;
                $this->k += $this->k0-1;
            }
        } else {
            $this->k0 =1;
        }
    }

}