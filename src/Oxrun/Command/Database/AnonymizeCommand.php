<?php

namespace Oxrun\Command\Database;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnonymizeCommand
 * @package Oxrun\Command\Database
 */
class AnonymizeCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;
    /**
     * Tables with no contents
     *
     * @var array
     */
    protected $anonymousTables = [
        'oxnewssubscribed' => [
            'fields' => [
                'OXFNAME' => 'string',
                'OXLNAME' => 'string',
                'OXEMAIL' => 'email',
            ],
            'where' => "t.`OXEMAIL` NOT LIKE '%{{keepDomain}}' AND t.`OXEMAIL` LIKE '%@%'"
        ],
        'oxuser' => [
            'fields' => [
                'OXUSERNAME' => 'email',
                'OXFNAME' => 'string',
                'OXLNAME' => 'string',
                'OXCOMPANY' => 'string',
                'OXADDINFO' => 'string',
                'OXSTREET' => 'string',
                'OXFON' => 'string',
                'OXPRIVFON' => 'string',
                'OXMOBFON' => 'string',
                'OXCITY' => 'string',
            ],
            'where' => "t.`OXUSERNAME` NOT LIKE '%{{keepDomain}}' AND t.`OXUSERNAME` LIKE '%@%'"
        ],
        'oxvouchers' => [
            'fields' => [
                'OXVOUCHERNR' => 'string',
            ],
            'where' => "1",
        ],
        'oxaddress' => [
            'fields' => [
                'OXCOMPANY' => 'string',
                'OXADDINFO' => 'string',
                'OXFNAME' => 'string',
                'OXLNAME' => 'string',
                'OXSTREET' => 'string',
                'OXFON' => 'string',
                'OXCITY' => 'string',
            ],
            'where' => "1",
        ],
        'oxorder' => [
            'fields' => [
                'OXBILLEMAIL' => 'email',
                'OXBILLCOMPANY' => 'string',
                'OXBILLFNAME' => 'string',
                'OXBILLLNAME' => 'string',
                'OXBILLUSTID' => 'string',
                'OXBILLSTREET' => 'string',
                'OXBILLADDINFO' => 'string',
                'OXBILLFON' => 'string',
                'OXBILLCITY' => 'string',
                'OXDELCOMPANY' => 'string',
                'OXDELFNAME' => 'string',
                'OXDELLNAME' => 'string',
                'OXDELSTREET' => 'string',
                'OXDELFON' => 'string',
                'OXDELCITY' => 'string',
                'OXDELFON' => 'string',
            ],
            'where' => "t.`OXBILLEMAIL` NOT LIKE '%{{keepDomain}}' AND t.`OXBILLEMAIL` LIKE '%@%'"
        ],
    ];

    /**
     * The default domain for username e.g.
     *
     * @var string
     */
    protected $anonymousDomain = "@oxrun.com";

    /**
     * Domain which should not be anonymized
     *
     * @var string
     */
    protected $keepDomain = "@foobar.com";
    
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('db:anonymize')
            ->setDescription('Anonymize relevant OXID db tables')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Debug SQL queries generated')
            ->addOption(
                'domain',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Domain to use for all anonymized usernames /email addresses, default is "@oxrun.com"'
            )
            ->addOption(
                'keepdomain',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Domain which should NOT be anonymized, default is "@foobar.com". Data with this domain in the email address will NOT be anonymized.'
            );

        $tables = print_r(array_keys($this->anonymousTables), true);
        $help = <<<HELP
Anonymizes user relevant data in the OXID database.
Relevant tables are:
{$tables}
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
        $keepdomain = $input->getOption('keepdomain');
        if ($keepdomain) {
            $this->keepDomain = $keepdomain;
        }
        $domain = $input->getOption('domain');
        if ($domain) {
            $this->anonymousDomain = $domain;
        }
        
        foreach ($this->anonymousTables as $tableName => $tableData) {
            $cols = $tableData['fields'];
            $where = $tableData['where'];
            $where = str_replace('{{keepDomain}}', $this->keepDomain, $where);
            $sQ = "UPDATE `{$tableName}` t SET ";
            $count = 0;
            foreach ($cols as $colname => $coltype) {
                $sQ .= "t.`{$colname}` = ";
                switch ($coltype) {
                    case "email":
                        $sQ .= "concat(md5(t.`{$colname}`),'{$this->anonymousDomain}') ";
                        break;
                    case "string":
                        $sQ .= "concat(left(t.`{$colname}`,1), repeat('*',(char_length(t.`{$colname}`)-1))) ";
                        break;
                }
                $count++;
                if ($count < count($cols)) {
                    $sQ .= ", ";
                }
            }
            $sQ .= " WHERE $where; ";

            if ($input->getOption('debug') === true) {
                $output->writeln('<info>' . $sQ . '</info>');
            }
            $returnValue = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sQ);
        }
        $output->writeln('<info>Anonymizing done.</info>');
    }
}
