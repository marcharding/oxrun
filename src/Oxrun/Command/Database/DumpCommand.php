<?php

namespace Oxrun\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DumpCommand
 * @package Oxrun\Command\Database
 */
class DumpCommand extends Command
{
    /**
     * Tables with no contents
     *
     * @var array
     */
    protected $anonymousTables = [
        'oxseo',
        'oxseologs',
        'oxseohistory',
        'oxuser',
        'oxuserbasketitems',
        'oxuserbaskets',
        'oxuserpayments',
        'oxnewssubscribed',
        'oxremark',
        'oxvouchers',
        'oxvoucherseries',
        'oxaddress',
        'oxorder',
        'oxorderarticles',
        'oxorderfiles',
        'oepaypal_order',
        'oepaypal_orderpayments',
    ];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('db:dump')
            ->setDescription('Dumps the the current shop database')
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Dump sql in to this file'
            )
            ->addOption(
                'table',
                't',
                InputOption::VALUE_REQUIRED,
                'name of table to dump only. Default all tables. Use comma separated list and or pattern e.g. %voucher%'
            )
            ->addOption(
                'ignoreViews',
                'i',
                InputOption::VALUE_NONE,
                'Ignore views'
            )
            ->addOption(
                'anonymous',
                'a',
                InputOption::VALUE_NONE,
                'Do not export table with person related data.'
            )
            ->addOption(
        'withoutTableData',
        'w',
        InputOption::VALUE_REQUIRED,
        'Table name to dump without data. Use comma separated list and or pattern e.g. %voucher%'
            );

        $anonymousTables= implode('`, `', $this->anonymousTables);
        $help = <<<HELP
Dump the current shop database.

<info>usage:</info>
    <comment>oxrun {$this->getName()} --withoutTableData oxseo,oxvou%</comment>
    - To dump all Tables, but `oxseo`, `oxvoucher`, and `oxvoucherseries` without data.
      possibilities: <comment>oxseo%,oxuser,%logs%</comment>
      
    <comment>oxrun {$this->getName()} --table %user%</comment>
    - to dump only those tables `oxuser` `oxuserbasketitems` `oxuserbaskets` `oxuserpayments` 

    <comment>oxrun {$this->getName()} --anonymous</comment> <info># Perfect for Stage Server</info>
    - Those table without data: `{$anonymousTables}`.
    
    <comment>oxrun {$this->getName()} -v </comment>
    - With verbose mode you will see the mysqldump command
      (`mysqldump -u 'root' -h 'oxid_db' -p ... `)
      
    <comment>oxrun {$this->getName()} --file dump.sql </comment>
    - Put the Output into a File
    
** Only existing tables will be exported. No matter what was required.
    
Requires <comment>php</comment>, <comment>exec</comment> and <comment>MySQL CLI tools</comment> installed on your system.
HELP;
        $this->setHelp($help);
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tablesNoData = $ignoreTables = $explicatedTable = [];
        $canDumpTables = true;

        $file   = $input->getOption('file');
        $dbName = \oxRegistry::getConfig()->getConfigParam('dbName');

        if($input->getOption('ignoreViews')) {
            $viewsResultArray = \oxDb::getDb()->getAll("SHOW FULL TABLES IN {$dbName} WHERE TABLE_TYPE LIKE 'VIEW'");
            if (is_array($viewsResultArray)) {
                foreach ($viewsResultArray as $sqlRow) {
                    $ignoreTables[] = $sqlRow[0];
                }
            }
        }

        if ($input->getOption('anonymous')) {
            $ignoreTables = array_merge($ignoreTables, $this->anonymousTables);
            $tablesNoData = array_merge($tablesNoData, $this->anonymousTables);
        }

        if ($input->getOption('withoutTableData')) {
            $argvData = $input->getOption('withoutTableData');
            $argvData = explode(',', $argvData);

            $ignoreTables = array_merge($ignoreTables, $argvData);
            $tablesNoData = array_merge($tablesNoData, $argvData);
        }

        if ($input->getOption('table')) {
            $argvData = $input->getOption('table');
            $argvData = explode(',', $argvData);
            $explicatedTable = $this->filterValidTables($argvData);
            if (empty($explicatedTable)) {
                $output->writeln('<error>No table found: `'. $input->getOption('table').'`</error>');
                exit(2);
            }
            $ignoreTables = [];
        }

        if (!empty($tablesNoData)) {
            $tables = $this->filterValidTables($tablesNoData);

            if (!empty($explicatedTable)) {
                $tables = array_intersect($tables, $explicatedTable);
                $explicatedTable = array_diff($explicatedTable, $tables);
                if (empty($explicatedTable)) {
                    $canDumpTables = false;
                }
            }

            if (!empty($tables)) {
                $tablesNoData = array_map('escapeshellarg', $tables);
                $tablesNoData = implode(' ', $tablesNoData);

                $commandOnlyTable = $this->getMysqlDumpCommand() . ' ' . $tablesNoData;
                $commandOnlyTable = sprintf($commandOnlyTable, ' --no-data');
                if ($file) {
                    $commandOnlyTable .= " > $file";
                }
            }
        }

        if ($ignoreTables) {
            $tables = $this->filterValidTables($ignoreTables);
            $ignoreTables = $this->addCommandFlag('--ignore-table=' . $dbName, $tables);
        }

        $ignoreTables = implode(' ', $ignoreTables);

        $commandTable = $this->getMysqlDumpCommand();
        $commandTable = sprintf($commandTable, $ignoreTables);
        if (!empty($explicatedTable)) {
            $explicatedTable = array_map('escapeshellarg', $explicatedTable);
            $commandTable .= implode(' ', $explicatedTable);
        }
        if ($file) {
            $saveOperator = !empty($tablesNoData) ? '>>' : '>';
            $commandTable .= " $saveOperator $file";
        }

        if (isset($commandOnlyTable)) {
            $output->writeln("<info>-- Dump Tables without data ...</info>");
            if ($output->getVerbosity() > $output::VERBOSITY_NORMAL) {
                $output->writeln('<comment>-- ' . $this->hiddePwd($commandOnlyTable) . '</comment>');
            }
            $this->executeCommand($input, $output, $commandOnlyTable);
        }

        if ($canDumpTables) {
            $output->writeln("<info>-- Dump Tables ...</info>");
            if ($output->getVerbosity() > $output::VERBOSITY_NORMAL) {
                $output->writeln('<comment>-- ' . $this->hiddePwd($commandTable) . '</comment>');
            }
            $this->executeCommand($input, $output, $commandTable);
        }
    }


    /**
     * Get the mysqldump cli command with user credentials.
     *
     * @return string
     */
    protected function getMysqlDumpCommand()
    {
        $dbHost = \oxRegistry::getConfig()->getConfigParam('dbHost');
        $dbUser = \oxRegistry::getConfig()->getConfigParam('dbUser');
        $dbName = \oxRegistry::getConfig()->getConfigParam('dbName');

        $dbPwd = \oxRegistry::getConfig()->getConfigParam('dbPwd');
        if (!empty($dbPwd)) {
            $dbPwd = ' -p' . $dbPwd;
        }

        $utfMode = '';
        if (\oxRegistry::getConfig()->getConfigParam('iUtfMode')) {
            $utfMode = ' --default-character-set=utf8';
        }

        $mysqldump = 'mysqldump' .
            ' -u ' . escapeshellarg($dbUser) .
            ' -h ' . escapeshellarg($dbHost) .
            $dbPwd .
            ' --force' .
            ' --quick' .
            ' --opt' .
            ' --hex-blob' .
            $utfMode .
            ' %s ' . # argumment part
            $dbName .
            ' '; # bash part

        return $mysqldump;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec') && $this->getApplication()->bootstrapOxid();
    }

    /**
     * @param string $flag
     * @param array $tables
     * @return array
     */
    protected function addCommandFlag($flag, $tables)
    {
        $flagged = [];

        foreach ($tables as $name) {
            $flagged[] = $flag . '.' . $name;
        }

        return $flagged;
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

        $result = \oxDb::getDb()->getAll($sqlstament);

        $existsTable = array_map(function ($row) {return $row[0];}, $result);

        return $existsTable;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $commandExportData
     * @param $returnValue
     * @param $commandOutput
     * @param $file
     */
    protected function executeCommand(InputInterface $input, OutputInterface $output, $command)
    {
        $error_file = tempnam(sys_get_temp_dir(), 'oxrun');
        $command .= ' 2>'.$error_file;

        exec($command, $commandOutput, $returnValue);

        if ($returnValue > 0) {
            $output->writeln('<error>' . file_get_contents($error_file) . '</error>');
            @unlink($error_file);
            return;
        }

        $file = $input->getOption('file');
        if (!empty($file)) {
            $output->writeln("<info>Dump {$file} created.</info>");
        } else {
            $output->writeln($commandOutput);
        }
        @unlink($error_file);
    }

    /**
     * @param $command
     * @return mixed
     */
    protected function hiddePwd($command)
    {
        return preg_replace('/-p[^ ]+/', '-p', $command);
    }

}

