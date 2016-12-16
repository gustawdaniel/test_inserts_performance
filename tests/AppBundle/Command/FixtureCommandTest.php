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

        $res = $this->conn->fetchColumn('select count(*) from main_1');
        $this->assertEquals($res,0);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>20, 'K'=>10]);
        $res = $this->conn->fetchColumn('select count(*) from main_1');
        $this->assertEquals($res,10);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>20, 'K'=>10, '--append'=>true]);
        $res = $this->conn->fetchColumn('select count(*) from main_1');
        $this->assertEquals($res,20);

        $res = $this->conn->fetchColumn('select count(*) from main_2');
        $this->assertEquals($res,0);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>50, 'K'=>5, '--append'=>true, 'main'=>2]);
        $res = $this->conn->fetchColumn('select count(*) from main_2');
        $this->assertEquals($res,5);
    }
}
