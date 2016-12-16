<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 03:15
 */

namespace Tests\AppBundle\Command;


use AppBundle\Command\FixtureCommand;
use AppBundle\Command\SchemaUpdateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class BaseTestCase extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected $output;

    private function resetDatabase()
    {
        (new CommandTester($this->app->find('doctrine:database:drop')))->execute(['--force' => true]);
        (new CommandTester($this->app->find('doctrine:database:create')))->execute([]);
        (new CommandTester($this->app->find('app:schema:update')))->execute(['--force' => true]);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->app = new Application(self::$kernel);

        $objects=[new DropCommand(),new CreateCommand(),new SchemaUpdateCommand(),new FixtureCommand()];
        foreach($objects as $object)
        {
            $this->app->add($object);
        }

        $this->resetDatabase();

        $this->conn = static::$kernel->getContainer()->get('doctrine')->getConnection();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->conn->close();
        $this->conn = null; // avoid memory leaks
    }

    protected function setCommand($commandName)
    {
        $this->commandTester = new CommandTester($this->app->find($commandName));
    }

    protected function doTest($array)
    {
//        var_dump($array);die();
        $this->commandTester->execute($array);
        $this->output = $this->commandTester->getDisplay();
    }
}