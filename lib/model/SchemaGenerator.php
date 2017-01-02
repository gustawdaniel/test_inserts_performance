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

    /**
     * @param $schema Schema
     */
    private function appendLogToSchema($schema)
    {
        $log = $schema->createTable("log");
        $log->addColumn("id", "integer",array("autoincrement"=>true,"unsigned" => true));
        $log->addColumn("n", "smallint",array("unsigned" => true));  // number of tables
        $log->addColumn("l", "smallint",array("unsigned" => true));  // number of rows in minor
        $log->addColumn("k", "integer",array("unsigned" => true));   // number of rows in major
        $log->addColumn("v", "string",array()); // number of measurement
        $log->addColumn("t", "float"); // time
        $log->addColumn("message", "string",array()); // description
        $log->setPrimaryKey(array("id"));
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