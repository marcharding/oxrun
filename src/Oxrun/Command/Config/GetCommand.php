<?php

namespace Oxrun\Command\Config;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetCommand
 * @package Oxrun\Command\Config
 */
class GetCommand extends Command implements \Oxrun\Command\EnableInterface
{

    use NeedDatabase;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('config:get')
            ->setDescription('Gets a config value')
            ->addArgument('variableName', InputArgument::REQUIRED, 'Variable name')
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
        $oxConfig = oxNew('oxConfig');
        $shopConfVar = $oxConfig->getShopConfVar(
            $input->getArgument('variableName'),
            $input->getOption('shopId'),
            $input->getOption('moduleId')
        );
        if (is_array($shopConfVar)) {
            $shopConfVar = json_encode($shopConfVar, true);
        }
        if( empty($shopConfVar)){
            $shopConfVar = 0;
        }
        $output->writeln("<info>{$input->getArgument('variableName')} has value {$shopConfVar}</info>");
    }
}