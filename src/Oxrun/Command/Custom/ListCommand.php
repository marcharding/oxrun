<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 */

namespace Oxrun\Command\Custom;

use Oxrun\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends \Symfony\Component\Console\Command\ListCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->checkDatabase($this->getApplication(), $output);
    }

    /**
     * @param $application
     * @param OutputInterface $output
     */
    protected function checkDatabase(Application $application, OutputInterface $output)
    {
        if ($application->bootstrapOxid(false) && $application->canConnectToDB() == false) {
            $output->writeln('');
            $output->writeln('<error>  Can\'t connect to Database. Most of the Commands are disabled </error>');
            $output->writeln('<error>  Error Message: ' . $application->getDatabaseConnection()->getLastErrorMsg() . '</error>');
        }
    }
}