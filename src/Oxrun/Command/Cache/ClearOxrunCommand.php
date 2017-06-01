<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <developer@tobimat.eu>
 */

namespace Oxrun\Command\Cache;

use Oxrun\Helper\ToolCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearOxrunCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('cache:clear:oxrun')
            ->setDescription('Clears the cache from this tool.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $toolCache = new ToolCache();
        $toolCache->clear();

        $output->writeln('<info>Oxrun cache cleared.</info>');
    }
}
