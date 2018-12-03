<?php

namespace Oxrun\Command\Module;

use Oxrun\Traits\ModuleListCheckTrait;
use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ActivateCommand
 * @package Oxrun\Command\Module
 */
class ActivateCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;
    use ModuleListCheckTrait;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:activate')
            ->setDescription('Activates a module')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkModulelist();

        $sModule = $input->getArgument('module');

        $oModule = oxNew('oxModule');
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        $oModuleInstaller = oxNew('oxModuleInstaller', $oModuleCache);

        if (!$oModule->load($sModule)) {
            $output->writeLn("<error>Cannot load module $sModule.</error>");
        }

        if (!$oModule->isActive()) {
            if ($oModuleInstaller->activate($oModule) === true) {
                $output->writeLn("<info>Module $sModule activated.</info>");
            } else {
                $output->writeLn("<error>Module $sModule could not be activated.</error>");
            }
        } else {
            $output->writeLn("<error>Module $sModule already activated.</error>");
        }
    }

}
