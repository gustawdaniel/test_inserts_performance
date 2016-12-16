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
//        $this->setCommand('app:schema:update');

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('1  log table with 0 rows', $this->output);

        $this->setCommand('app:test');
        $this->doTest(['name'=>'first','N'=>1,'L'=>1,'K'=>1]);

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('1  log table with 1 rows', $this->output);

        $this->setCommand('app:test');
        $this->doTest(['name'=>'first','N'=>1,'L'=>1,'K'=>1]);

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('1  log table with 2 rows', $this->output);

        $this->setCommand('app:test');
        $this->doTest(['name'=>'first','N'=>1,'L'=>1,'K'=>1, 'no-log'=>true]);

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('1  log table with 2 rows', $this->output);
    }
}