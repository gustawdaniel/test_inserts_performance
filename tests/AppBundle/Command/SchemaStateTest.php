<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 13:44
 */

namespace Tests\AppBundle\Command;


class SchemaStateTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->setCommand('app:schema:state');

        $this->doTest([]);
        $this->assertContains('10 minor tables with 0 rows', $this->output);

        $this->setCommand('app:schema:update');
        $this->doTest(['--force'=>true,'N'=>5]);
        $this->assertContains('Database schema updated successfully!',$this->output);

        $this->setCommand('app:fixtures:load');
        $this->doTest(['table'=>'minor','N'=>5, 'L'=>60]);
        $this->assertContains('Created 60 rows in 5 tables. In sum 300 inserts.',$this->output);

        $this->doTest(['table'=>'major','N'=>5, 'L'=>60, 'K'=>20]);
        $this->assertContains('Created 20 rows (1->20) in table major_1.',$this->output);

        $this->doTest(['table'=>'major','N'=>5, 'L'=>60, 'K'=>70, 'major'=>2]);
        $this->assertContains('Created 70 rows (1->70) in table major_2.',$this->output);

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('5 minor tables with 60 rows.', $this->output);
        $this->assertContains('1  major_1 table with 20 rows.', $this->output);
        $this->assertContains('1  major_2 table with 70 rows.', $this->output);
        $this->assertContains('1  log table with 0 rows.', $this->output);

        $this->setCommand('app:fixtures:load');
        $this->doTest(['table'=>'major','N'=>5, 'L'=>60, 'K'=>0]);
        $this->assertContains('Created 0 rows (1->0) in table major_1.',$this->output);

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('1  major_1 table with 0 rows', $this->output);

        $this->setCommand('doctrine:database:drop');
        $this->doTest(['--force'=>true]);
        $this->setCommand('doctrine:database:create');
        $this->doTest([]);

        $this->setCommand('app:schema:state');
        $this->doTest([]);
        $this->assertContains('Schema is incorrect!', $this->output);

    }
}