<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 13:49
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class Base extends ContainerAwareCommand
{
    /**
     * @var Connection
     */
    protected $conn;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
//        var_dump($input->getArguments());
//        var_dump($input->getOptions());
//        var_dump(gettype($input->getArgument('K')));die();

        $this->conn = $this->getContainer()->get('doctrine')->getConnection();

    }

}