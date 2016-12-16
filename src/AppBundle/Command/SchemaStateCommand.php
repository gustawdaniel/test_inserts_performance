<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16.12.16
 * Time: 13:48
 */

namespace AppBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaStateCommand extends Base
{
    protected function configure()
    {
        $this->setName('app:schema:state');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->conn->getDatabase()){
            $output->writeln('Database doe\'s not exists!');
            unset($this->conn); return 0;
        }

        $queries = [
            'tables' => ["SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?",[$this->conn->getDatabase()]]
        ];

        if($queries['tables']<4){
            $output->writeln('Schema is incorrect!');
            unset($this->conn); return 0;
        }

        $tables = ['minor_1','major_1','major_2','log'];

        foreach($tables as $table)
        {
            $queries[$table.'_rows'] = ['SELECT COUNT(*) FROM '.$table.' where 1=?;',[1]];
        }

        foreach($queries as $name =>$query)
        {

//        $pre = $this->conn->prepare($query[0]);
//        $pre->bindValue($query[1][0]);
////            $pre->execute();
//
//            var_dump($pre->bindParam($query[1]));
//            die("ok");
            $res[$name] = $this->conn->fetchColumn($query[0],$query[1]);
        }

        $output->writeln('States of database:');
        $output->writeln('<info>'.($res['tables']-3).'</info> minor tables with <info>'. $res['minor_1_rows'] . '</info> rows.');
        $output->writeln('<info> 1 </info> major_1 table with <info>'. $res['major_1_rows'] . '</info> rows.');
        $output->writeln('<info> 1 </info> major_2 table with <info>'. $res['major_2_rows'] . '</info> rows.');
        $output->writeln('<info> 1 </info> log table with <info>'. $res['log_rows'] . '</info> rows.');
    }

}