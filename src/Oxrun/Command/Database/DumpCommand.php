<?php

namespace Oxrun\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
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
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('db:dump')
            ->setDescription('Dumps the the current shop database')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Dump sql in to this file')
            ->addOption('ignoreViews', null, InputOption::VALUE_NONE, 'Ignore views');

        $help = <<<HELP
Dumps the the current shop database.

Requires php exec and MySQL CLI tools installed on your system.
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
        // allow empty password
        $dbPwd = \oxRegistry::getConfig()->getConfigParam('dbPwd');
        if (!empty($dbPwd)) {
            $dbPwd = '-p' . $dbPwd;
        }

        $file = $input->getOption('file');
        if (!empty($file)) {
            $file = "--result-file=" . $file;
        } else {
            $file = "";
        }

        if($input->getOption('ignoreViews')) {
            $dbName = \oxRegistry::getConfig()->getConfigParam('dbName');
            $viewsResultArray = \oxDb::getDb()->getArray("SHOW FULL TABLES IN {$dbName} WHERE TABLE_TYPE LIKE 'VIEW'");
            $ignoreViewTables = array();
            foreach($viewsResultArray as $viewArray) {
                $ignoreViewTables[] = '--ignore-table=' . $dbName . '.' . $viewArray[0];
            }
            $ignoreViewTables = implode(' ', $ignoreViewTables);
        }

        $exec = sprintf(
            "mysqldump -h%s %s -u%s %s %s %s 2>&1",
            \oxRegistry::getConfig()->getConfigParam('dbHost'),
            $dbPwd,
            \oxRegistry::getConfig()->getConfigParam('dbUser'),
            \oxRegistry::getConfig()->getConfigParam('dbName'),
            $ignoreViewTables,
            $file
        );

        exec($exec, $commandOutput, $returnValue);

        if ($returnValue > 0) {
            $output->writeln('<error>' . implode(PHP_EOL, $commandOutput) . '</error>');
            return;
        }

        if (!empty($file)) {
            $output->writeln("<info>Dump {$input->getOption('file')} created.</info>");
        } else {
            $output->writeln($commandOutput);
        }

    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec') && $this->getApplication()->bootstrapOxid();
    }

}