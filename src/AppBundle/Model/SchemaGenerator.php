<?php

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 14.12.16
 * Time: 11:13
 */
namespace AppBundle\Model;

use AppBundle\Controller\BaseController;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

class SchemaGenerator
{
    /**
     * @var Table[]
     */
    private $main, $minor;

    private $N;

    public function __construct($n)
    {
        $this->main = [];
        $this->minor = [];
        $this->N = $n ? $n : BaseController::N;
    }

    /**
     * @param $schema Schema
     */
    private function appendMinorToSchema($schema)
    {
        for($i=1;$i<=$this->N;$i++) {
            $this->minor[$i] = $schema->createTable("minor_".$i);
            $this->minor[$i]->addColumn("id", "integer");
            $this->minor[$i]->setPrimaryKey(array("id"));
        }
    }

    /**
     * @param $schema Schema
     */
    private function appendMainToSchema($schema)
    {
        for($i=1;$i<=2;$i++)
        {
            $this->main[$i] = $schema->createTable("main_".$i);
            $this->main[$i]->addColumn("id", "integer");
            for($j=1;$j<=$this->N;$j++)
            {
                $this->main[$i]->addColumn("minor_".$j."_id", "integer");
                $this->main[$i]->addForeignKeyConstraint($this->minor[$j], array("minor_".$j."_id"), array("id"));
            }
            $this->main[$i]->setPrimaryKey(array("id"));
        }
    }

    /**
     * @param $schema Schema
     */
    private function appendLogToSchema($schema)
    {
        $log = $schema->createTable("log");
        $log->addColumn("id", "integer",array("autoincrement"=>true,"unsigned" => true));
        $log->addColumn("n", "smallint",array("unsigned" => true));
        $log->addColumn("l", "smallint",array("unsigned" => true));
        $log->addColumn("k0", "integer",array("unsigned" => true));
        $log->addColumn("k", "integer",array("unsigned" => true));
        $log->addColumn("execution_time", "float");
        $log->addColumn("operation", "string",array());
        $log->setPrimaryKey(array("id"));
    }

    /**
     * @return Schema Schema
     */
    public function generate()
    {
        $schema=new Schema();

        $this->appendMinorToSchema($schema);
        $this->appendMainToSchema($schema);
        $this->appendLogToSchema($schema);

        return $schema;
    }
}