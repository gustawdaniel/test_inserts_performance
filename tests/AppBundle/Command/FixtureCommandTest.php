<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 02:58
 */

namespace Tests\AppBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;

class FixtureCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->setCommand('app:fixture:load');

        $this->doTest(['table' => 'minor']);
        $res = $this->conn->fetchColumn('SELECT COUNT(*) FROM minor_1');
        $this->assertEquals($res,static::$kernel->getContainer()->getParameter('L'));
    }
}
