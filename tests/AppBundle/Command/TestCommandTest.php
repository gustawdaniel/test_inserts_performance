<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 16:27
 */

namespace Tests\AppBundle\Command;

class TestCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->setCommand('app:schema:update');

        $log1 = $this->conn->fetchColumn("SELECT COUNT(*) FROM log");
        var_dump($log1);


        $this->setCommand('app:test');
        $this->doTest(['name'=>'first','N'=>1,'L'=>1,'K'=>1]);

        $log2 = $this->conn->fetchColumn("SELECT COUNT(*) FROM log");
        var_dump($log2);

        $this->setCommand('app:test');
        $this->doTest(['name'=>'first','N'=>1,'L'=>1,'K'=>1]);

        $log3 = $this->conn->fetchColumn("SELECT COUNT(*) FROM log");
        var_dump($log3);

        $this->setCommand('app:test');
        $this->doTest(['name'=>'first','N'=>1,'L'=>1,'K'=>1, '--no-log'=>true]);

        $log4 = $this->conn->fetchColumn("SELECT COUNT(*) FROM log");
        var_dump($log4);


        $this->assertEquals(0,$log1);
        $this->assertEquals($log3,$log4);
        $this->assertGreaterThan($log1,$log2);
        $this->assertGreaterThan($log2,$log3);
    }
}