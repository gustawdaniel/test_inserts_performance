<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 15.12.16
 * Time: 19:14
 */

namespace Tests\AppBundle\Command;

use AppBundle\Command\SchemaUpdateCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;

class SchemaUpdateCommandTest extends KernelTestCase
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->conn = static::$kernel->getContainer()->get('doctrine')->getConnection();
        $app = new Application(self::$kernel);
        $app->add(new SchemaUpdateCommand());
        $this->command = $app->find('app:schema:update');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
        ));
        $this->assertContains('Please run the operation by passing one - or both - of the following options:', $this->commandTester->getDisplay());

        $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'N' => 1, '--force' => true, '--dump-sql' => true
        ));
        $this->assertContains('ALTER TABLE', $this->commandTester->getDisplay());


        $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'N' => 2, '--force' => true
        ));

        $this->assertContains('Database schema updated successfully!', $this->commandTester->getDisplay());

    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->conn->close();
        $this->conn = null; // avoid memory leaks
    }
}