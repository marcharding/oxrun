<?php

namespace Oxrun\Command\Views;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 * @package Oxrun\Command\Views
 */
class UpdateCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('views:update')
            ->setDescription('Updates the views');
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
        $myConfig->setConfigParam('blSkipViewUsage', true);
        
        $oMetaData = \oxNew('oxDbMetaDataHandler');
        if ($oMetaData->updateViews()) {
            $output->writeln('<info>Views updated.</info>');
        } else {
            $output->writeln('<error>Views could not be updated.</error>');
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        /** @var \Oxrun\Application $application */
        $application = $this->getApplication();
        return $application->bootstrapOxid(true);
    }

}
