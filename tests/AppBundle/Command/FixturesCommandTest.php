<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 02:58
 */

namespace Tests\AppBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;

class FixturesCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->setCommand('app:fixtures:load');

        $this->doTest(['table' => 'minor']);
        $res = $this->conn->fetchColumn('SELECT COUNT(*) FROM minor_1');
        $this->assertEquals($res,static::$kernel->getContainer()->getParameter('L'));

        $res = $this->conn->fetchColumn('select count(*) from major_1');
        $this->assertEquals($res,0);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>20, 'K'=>10]);
        $res = $this->conn->fetchColumn('select count(*) from major_1');
        $this->assertEquals($res,10);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>20, 'K'=>10, '--append'=>true]);
        $res = $this->conn->fetchColumn('select count(*) from major_1');
        $this->assertEquals($res,20);

        $res = $this->conn->fetchColumn('select count(*) from major_2');
        $this->assertEquals($res,0);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>50, 'K'=>5, '--append'=>true, 'major'=>2]);
        $res = $this->conn->fetchColumn('select count(*) from major_2');
        $this->assertEquals($res,5);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>50, 'K'=>0, '--no-transaction'=>true, 'major'=>2]);
        $res = $this->conn->fetchColumn('select count(*) from major_2');
        $this->assertEquals($res,0);

        $t1=microtime(true);
        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>50, 'K'=>1000, '--no-transaction'=>true, 'major'=>2]);
        $res = $this->conn->fetchColumn('select count(*) from major_2');
        $this->assertEquals($res,1000);
        $t2=microtime(true);

        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>50, 'K'=>0, 'major'=>2]);
        $res = $this->conn->fetchColumn('select count(*) from major_2');
        $this->assertEquals($res,0);

        $t3=microtime(true);
        $this->doTest(['table'=>'major', 'N'=>10, 'L'=>50, 'K'=>1000, 'major'=>2]);
        $res = $this->conn->fetchColumn('select count(*) from major_2');
        $this->assertEquals($res,1000);
        $t4=microtime(true);

        $this->assertGreaterThanOrEqual($t4-$t3,$t2-$t1);

    }

    function milliseconds() {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }
}
