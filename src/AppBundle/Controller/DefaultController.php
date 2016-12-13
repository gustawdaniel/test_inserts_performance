<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Comparator;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Route("/{parameter}")
     */
    public function indexAction($parameter)
    {
        $conn = $this->getDoctrine()->getConnection();

        $sm = $conn->getSchemaManager();
        $myPlatform = $conn->getDatabasePlatform();

        $schema = new Schema();

        $myTable = $schema->createTable("my_table");
        $myTable->addColumn("id", "integer");
        $myTable->addColumn("username", "string", array("length" => 32));
        $myTable->setPrimaryKey(array("id"));
        $myTable->addUniqueIndex(array("username"));

        $myForeign = $schema->createTable("my_foreign");
        $myForeign->addColumn("id", "integer");
        $myForeign->addColumn("user_id", "integer");
        $myForeign->addForeignKeyConstraint($myTable, array("user_id"), array("id"));

        $queries = $schema->toSql($myPlatform); // get queries to create this schema.

        $fromSchema = $sm->createSchema();
        $toSchema = $schema;

        $comparator = new Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        $queries2 = $schemaDiff->toSql($myPlatform);
        $queries3 = $fromSchema->toSql($myPlatform);


        $sm = $conn->getSchemaManager();
        $tables = $sm->listTables();
        foreach ($tables as $table) {
            echo $table->getName() . " columns:\n\n";
            foreach ($table->getColumns() as $column) {
                echo ' - ' . $column->getName() . "\n";
            }
        }

        if($parameter=="do")
        {
            foreach($queries as $query) {
                $statement = $conn->prepare($query);
                $statement->execute();
            }
        }


        return new JsonResponse(
            ["doctrine"=>$queries,
            "alter"=>$queries2,
            "curent"=>$queries3]
        );
    }
}
