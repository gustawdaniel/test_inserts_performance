<?php

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 14.12.16
 * Time: 11:13
 */
namespace Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Comparator;

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
        $this->N = $n;
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
            $this->main[$i] = $schema->createTable("major_".$i);
            $this->main[$i]->addColumn("id", "integer");
            for($j=1;$j<=$this->N;$j++)
            {
                $this->main[$i]->addColumn("minor_".$j."_id", "integer");
                $this->main[$i]->addForeignKeyConstraint($this->minor[$j], array("minor_".$j."_id"), array("id"));
            }
            $this->main[$i]->setPrimaryKey(array("id"));
        }
    }

//    /**
//     * @param $schema Schema
//     * @return Table
//     */
//    private function appendMachineToSchemaAndGetIt($schema)
//    {
//
//
//        return $machine;
//    }

    /**
     * @param $schema Schema
     */
    private function appendLogToSchema($schema)
    {
//        "autoincrement"=>true, 'strategy'=>'UUID'
        $machine = $schema->createTable("machine");
        $machine->addColumn("id", "guid",array());
        $machine->addColumn("name", "string",[]);  // number of tables
        $machine->addColumn("write", "float");  // number of rows in minor
        $machine->addColumn("read", "float");  // number of rows in minor
        $machine->addColumn("latency", "float");  // number of rows in minor
        $machine->addColumn("cpu", "float");   // number of rows in major
        $machine->setPrimaryKey(array("id"));



        /**
         * @ORM\Column(type="guid")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="UUID")
         */

        $log = $schema->createTable("log");
        $log->addColumn("id", "integer",array("autoincrement"=>true,"unsigned" => true));
        $log->addColumn("n", "smallint",array("unsigned" => true));  // number of tables
        $log->addColumn("l", "integer",array("unsigned" => true));  // number of rows in minor
        $log->addColumn("k", "integer",array("unsigned" => true));   // number of rows in major
        $log->addColumn("t", "float"); // time
        $log->addColumn("message", "string",array()); // description
        $log->addColumn("machine_id", "guid"); // description
        $log->addForeignKeyConstraint($machine, array("machine_id"), array("id"));
        $log->setPrimaryKey(array("id"));
//
//        $log->addColumn("v", "string",array()); // number of measurement
    }

    /**
     * @param Boolean $minor
     * @param Boolean $major
     * @return Schema Schema
     */
    private function generate($minor,$major)
    {
        $schema=new Schema();

        $minor && $this->appendMinorToSchema($schema);
        $major && $this->appendMainToSchema($schema);

//        $machine = $this->appendMachineToSchemaAndGetIt($schema);
        $this->appendLogToSchema($schema);

        return $schema;
    }

    /**
     * @param Connection $conn
     * @throws \Exception $conn
     */
    public function apply($conn)
    {
        $this->execute($this->generate(false,false),$conn); // delete all table leaving log only prepare base before regeneration
        $this->execute($this->generate(true,true),$conn); // create all tables
    }

    /**
     * @param Schema $schema
     * @param Connection $conn
     * @throws \Doctrine\DBAL\DBALException
     */
    private function execute(Schema $schema, Connection $conn)
    {
        $queries = (new Comparator())->compare($conn->getSchemaManager()->createSchema(), $schema)->toSql($conn->getDatabasePlatform());

        foreach($queries as $key => $query) {
            $conn->prepare($query)->execute();
        }
    }
}