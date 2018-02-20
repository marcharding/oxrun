<?php
/**
 * Created by oxid-commandling.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 09.12.17
 * Time: 21:11
 */

namespace Oxrun\Command\Database;

use Oxrun\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:list')
            ->setDescription('List of all Tables')

            ->addOption(
                'plain',
                'p',
                InputOption::VALUE_NONE,
                'print list as comma separated.'
            )
            ->addOption(
                'pattern',
                't',
                InputOption::VALUE_REQUIRED,
                'table name pattern test. e.g. oxseo%,oxuser'
            );

        $help = <<<HELP
List Tables

<info>usage:</info>
    <comment>oxrun {$this->getName()} --pattern oxseo%,oxuser</comment>
    - To dump all Tables, but `oxseo`, `oxvoucher`, and `oxvoucherseries` without data.
      possibilities: <comment>oxseo%,oxuser,%logs%</comment>
      

HELP;
        $this->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tables = ['%'];

        if ($input->getOption('pattern')) {
            $argvData = $input->getOption('pattern');
            $tables = explode(',', $argvData);
        }

        $tablenames = $this->filterValidTables($tables);

        if ($input->getOption('plain')) {
            $quote = "'";
            $existsTable = array_map(function ($row) {return $row[0];}, $tablenames);
            $list = implode("$quote, $quote", $existsTable);
            $output->writeln($quote . $list . $quote);
            return;
        }

        /** @var TableHelper $table */
        $table = $this->getHelper('table');
        $table->setHeaders(['Table', 'Type']);
        $table->addRows($tablenames);

        $table->render($output);
    }

    /**
     * @param array $tables
     * @return array
     */
    protected function filterValidTables($tables)
    {
        $whereIN = $whereLIKE = [];

        $dbName = \oxRegistry::getConfig()->getConfigParam('dbName');

        foreach ($tables as $name) {
            if (preg_match('/[%*]/', $name)) {
                $name = str_replace(['_','*'], ['\\_', '%'], $name);
                $whereLIKE[] = $name;
            } else {
                $whereIN[] = $name;
            }
        }

        $whereIN = implode("', '", $whereIN);
        $conditionsIN = "Tables_in_{$dbName} IN ('{$whereIN}')";

        $conditionsLIKE = '';
        if (!empty($whereLIKE)) {
            $template = " OR Tables_in_{$dbName} LIKE ('%s')";
            foreach ($whereLIKE as $tablename) {
                $conditionsLIKE .= sprintf($template, $tablename);
            }
        }

        $sqlstament = "SHOW FULL TABLES IN {$dbName} WHERE $conditionsIN $conditionsLIKE";

        $existsTable = \oxDb::getDb()->getAll($sqlstament);

        return $existsTable;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }
}