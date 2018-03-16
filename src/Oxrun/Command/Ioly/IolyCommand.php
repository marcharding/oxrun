<?php

namespace Oxrun\Command\Ioly;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IolyCommand
 * @package Oxrun\Command\Ioly
 */
class IolyCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('ioly')
            ->setDescription('Executes the ioly package installer.')
            ->addArgument(
                'ioly-variables',
                InputArgument::IS_ARRAY,
                'The ioly command arguments.'
            );
    }

    /**
     * Executes the ioly command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argv = array('ioly');
        $argv = array_merge($argv, $input->getArgument('ioly-variables'));
        $_SERVER['IOLY_SYSTEM_BASE'] = $this->getApplication()->getShopDir();
        $_SERVER['IOLY_SYSTEM_VERSION'] = $this->getApplication()->getOxidVersion();
        include getenv('IOLY_PHP');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $ioly = getenv('IOLY_PHP');
        if (!empty($ioly) && is_file($ioly) ) {
            return true;
        }
        return false;
    }

}