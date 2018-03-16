<?php

namespace Oxrun\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class QueryCommand
 * @package Oxrun\Command\Database
 */
class QueryCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('db:query')
            ->setDescription('Executes a query')
            ->addArgument('query', InputArgument::REQUIRED, 'The query which is to be executed')
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Raw output');

        $help = <<<HELP
Executes an SQL query on the current shop database. Wrap your SQL in quotes.

If your query produces a result (e.g. a SELECT statement), the output will be returned via the table component. Add the raw option for raw output.

Requires php exec and MySQL CLI tools installed on your system.
HELP;
        $this->setHelp($help);
    }

    /**
     * Returns the query string with escaped ' characters so it can be used
     * within the mysql -e argument.
     *
     * The -e argument is enclosed by single quotes. As you can't escape
     * the single quote within the single quote, you have to end the quote,
     * then escape the single quote character and reopen the quote.
     *
     * @param string $query
     * @return string
     */
    protected function getEscapedSql($query)
    {
        return str_replace("'", "'\''", $query);
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $this->getEscapedSql($input->getArgument('query'));

        // allow empty password
        $dbPwd = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbPwd');
        if (!empty($dbPwd)) {
            $dbPwd = '-p' . $dbPwd;
        }

        $exec = sprintf(
            "mysql -h%s %s -u%s %s -e '%s' 2>&1",
            \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbHost'),
            $dbPwd,
            \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbUser'),
            \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('dbName'),
            $query
        );

        exec($exec, $commandOutput, $returnValue);

        if ($returnValue > 0) {
            $output->writeln('<error>' . implode(PHP_EOL, $commandOutput) . '</error>');
            return;
        }

        if ($input->getOption('raw') === true) {
            $output->writeln($commandOutput);
            return;
        }

        $commandOutput = array_map(
            function ($row) {
                return explode("\t", $row);
            }, $commandOutput
        );

        $table = new Table($output);
        $table->setHeaders(array_shift($commandOutput))->setRows($commandOutput);
        $table->render();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec') && $this->getApplication()->bootstrapOxid();
    }

}