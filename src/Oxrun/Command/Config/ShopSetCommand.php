<?php

namespace Oxrun\Command\Config;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShopSetCommand
 * @package Oxrun\Command\Config
 */
class ShopSetCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('config:shop:set')
            ->setDescription('Sets a shop config value')
            ->addArgument('variableName', InputArgument::REQUIRED, 'Variable name')
            ->addArgument('variableValue', InputArgument::REQUIRED, 'Variable value');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oxShop = oxNew('oxShop');
        $oxShop->load($input->getOption('shopId'));
        $oxShop->assign(array('oxshops__' . $input->getArgument('variableName') => $input->getArgument('variableValue')));
        $oxShop->save();
        $output->writeln("<info>Shopconfig {$input->getArgument('variableName')} set to {$input->getArgument('variableValue')}</info>");
    }
}