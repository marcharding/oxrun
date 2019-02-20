<?php

namespace Oxrun\Command\Config;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShopGetCommand
 * @package Oxrun\Command\Config
 */
class ShopGetCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('config:shop:get')
            ->setDescription('Sets a shop config value')
            ->addArgument('variableName', InputArgument::REQUIRED, 'Variable name');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Shop config
        $oxShop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
        $oxShop->load($input->getOption('shopId'));
        $varibaleValue = $oxShop->{'oxshops__' . $input->getArgument('variableName')}->value;
        $output->writeln("<info>Config {$input->getArgument('variableName')} has value $varibaleValue</info>");
    }

}
