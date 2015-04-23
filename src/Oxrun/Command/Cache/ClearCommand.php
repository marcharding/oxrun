<?php

namespace Oxrun\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearCommand
 * @package Oxrun\Command\Cache
 */
class ClearCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clears the cache');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $myConfig = \oxRegistry::getConfig();
        foreach (glob($myConfig->getConfigParam('sCompileDir') . '/*') as $filename) {
            if (!is_dir($filename)) {
                unlink($filename);
            }
        }
        foreach (glob($myConfig->getConfigParam('sCompileDir') . '/smarty/*') as $filename) {
            if (!is_dir($filename)) {
                unlink($filename);
            }
        }
        $output->writeln('<info>Cache cleared.</info>');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }

}