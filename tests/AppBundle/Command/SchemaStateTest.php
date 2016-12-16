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
    }
}