<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 15.12.16
 * Time: 19:14
 */

namespace Tests\AppBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

class SchemaUpdateCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->setCommand('app:schema:update');

        $this->doTest([]);
        $this->assertContains('Please run the operation by passing one - or both - of the following options:', $this->output);

        $this->doTest(['N' => 1, '--force' => true, '--dump-sql' => true]);
        $this->assertContains('ALTER TABLE', $this->output);

        $this->doTest(['N' => 2, '--force' => true]);
        $this->assertContains('Database schema updated successfully!', $this->output);
    }
}