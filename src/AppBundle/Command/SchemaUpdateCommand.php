<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 15.12.16
 * Time: 16:46
 */

namespace AppBundle\Command;


use Symfony\Component\Console\Input\InputOption;
use AppBundle\Model\SchemaGenerator;
use Doctrine\DBAL\Schema\Comparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;

class SchemaUpdateCommand extends Base
{
    private $n,$q;


    public function configure()
    {
        $this
            ->setName('app:schema:update')
            ->addOption('dump-sql', null, InputOption::VALUE_NONE,'Dumps the generated SQL statements to the screen (does not execute them).')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Causes the generated SQL statements to be physically executed against your database.')
            ->addArgument('N', InputArgument::OPTIONAL, 'Number of minor tables');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->n = $input->getArgument('N') ?: $this->getContainer()->getParameter('N');
        $this->q = $input->getOption('quiet');

        if (!$input->getOption('force') && !$input->getOption('dump-sql') ) {
            $output->writeln('Please run the operation by passing one - or both - of the following options:');
            $output->writeln(sprintf('    <info>%s --force</info> to execute the command', $this->getName()));
            $output->writeln(sprintf('    <info>%s --dump-sql</info> to dump the SQL statements to the screen', $this->getName()));
            return 0;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = (new SchemaGenerator($this->n))->generate();
        $queries = (new Comparator())->compare($this->conn->getSchemaManager()->createSchema(), $schema)->toSql($this->conn->getDatabasePlatform());

        if (!$queries) { $output->writeln('Nothing to update - your database is sync with given state.'); $this->conn->close(); return 0; }
        if ($input->getOption('dump-sql')) { $output->writeln(implode(';' . PHP_EOL, $queries) . ';');}
        if ($input->getOption('force')) { $this->rebuildDatabase($output,$queries); }
        $this->conn->close();
    }

    private function rebuildDatabase(OutputInterface $output, $queries)
    {
        if(!$this->q) {
            $output->writeln('Updating database schema...');

            $progress = new ProgressBar($output, count($queries));
            $progress->setFormat('very_verbose');
            $progress->start();
        }
        foreach($queries as $key => $query) {
            $this->conn->prepare($query)->execute();
            $this->q?:$progress->advance();
        }
        if(!$this->q) {
            $progress->finish();

            $pluralization = (1 === count($queries)) ? 'query was' : 'queries were';
            $output->writeln(sprintf("\n".'Database schema updated successfully! "<info>%s</info>" %s executed', count($queries), $pluralization));
        }
    }
}