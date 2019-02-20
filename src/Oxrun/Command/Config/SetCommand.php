<?php

namespace Oxrun\Command\Config;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetCommand
 * @package Oxrun\Command\Config
 */
class SetCommand extends Command implements \Oxrun\Command\EnableInterface
{

    use NeedDatabase;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('config:set')
            ->setDescription('Sets a config value')
            ->addArgument('variableName', InputArgument::REQUIRED, 'Variable name')
            ->addArgument('variableValue', InputArgument::REQUIRED, 'Variable value')
            ->addOption('variableType', null, InputOption::VALUE_REQUIRED, 'Variable type')
            ->addOption('moduleId', null, InputOption::VALUE_OPTIONAL, '');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // do not use the registry pattern (\oxRegistry::getConfig()) here, so we do not have any caches (breaks unit tests)
        $oxConfig = oxNew('oxConfig');

        // determine variable type
        if ($input->getOption('variableType')) {
            $variableType = $input->getOption('variableType');
        } else {
            $sql = sprintf(
                "SELECT  `oxconfig`.`OXVARTYPE` FROM `oxconfig` WHERE `oxconfig`.`OXVARNAME` = %s",
                \oxDb::getDb()->quote($input->getArgument('variableName'))
            );
            $variableType = \oxDb::getDb()->getOne($sql);
        }

        if (in_array($variableType, array('aarr', 'arr'))) {
            $variableValue = json_decode($input->getArgument('variableValue'), true);
        } else {
            $variableValue = $input->getArgument('variableValue');
        }

        $oxConfig->saveShopConfVar(
            $variableType,
            $input->getArgument('variableName'),
            $variableValue,
            $input->getOption('shopId'),
            $input->getOption('moduleId')
        );

        $output->writeln("<info>Config {$input->getArgument('variableName')} set to {$input->getArgument('variableValue')}</info>");
    }

}