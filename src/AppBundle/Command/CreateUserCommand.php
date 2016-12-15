<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 15.12.16
 * Time: 03:09
 */

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\DBAL\Driver\Connection;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:create-user')
            ->setDescription('Create new user')
            ->setHelp('This command allows you to create users...')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $n=10;$l=50;$k=1000;$k0=1;$main=1;$transaction=true;

        /** @var Connection $conn */
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        if($k0==1) { $conn->delete('main_'.$main,[1=>1]); }
        if($k>1e4) {
            set_time_limit(0);
            ini_set("max_execution_time", 0);
        }
        if($transaction) {$conn->beginTransaction();}
        try{
            for($i=$k0;$i<=$k;$i++){                // row in table main
                $content = ['id'=>$i];
                for($j=1;$j<=$n;$j++){            // foreign key of row
                    $content['minor_'.$j.'_id'] = rand(1,$l);
                }
                $conn->insert('main_'.$main, $content);
            }
            if($transaction) {$conn->commit();}
        } catch(\Exception $e) {
            if($transaction) {$conn->rollBack();}
            throw $e;
        }



        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        $output->writeln('Username: '.$input->getArgument('username'));

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');

        // green text
        $output->writeln('<info>foo</info>');

// green text
        $output->writeln('<fg=magenta>foo</>');


        $output->write('create a user.'."\n");

        $output->writeln(array(
            '<info>Lorem Ipsum Dolor Sit Amet</>',
            '<info>==========================</>',
            '',
        ));
    }

}