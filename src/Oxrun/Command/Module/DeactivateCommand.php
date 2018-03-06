<?php

namespace Oxrun\Command\Module;

use Oxrun\Traits\ModuleListCheckTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeactivateCommand
 * @package Oxrun\Command\Module
 */
class DeactivateCommand extends Command
{
    use ModuleListCheckTrait;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:deactivate')
            ->setDescription('Deactivates a module')
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

        $oxidVersion = $this->getApplication()->getOxidVersion();
        if (version_compare($oxidVersion, '4.9.0') >= 0) {
            $this->executeVersion490($input, $output);
        } elseif (version_compare($oxidVersion, '4.8.0') >= 0) {
            $this->executeVersion480($input, $output);
        } elseif (version_compare($oxidVersion, '4.7.0') >= 0) {
            $this->executeVersion470($input, $output);
        }
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function executeVersion490(InputInterface $input, OutputInterface $output)
    {
        $sModule = $input->getArgument('module');

        $oModule = \oxNew('oxModule');
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        $oModuleInstaller = oxNew('oxModuleInstaller', $oModuleCache);

        if (!$oModule->load($sModule)) {
            $output->writeLn("<error>Cannot load module $sModule.</error>");
        }

        if (!$oModule->isActive()) {
            $output->writeLn("<error>Module $sModule already deactivated.</error>");
        } else {
            if ($oModuleInstaller->deactivate($oModule) === true) {
                $output->writeLn("<info>Module $sModule deactivated.</info>");
            } else {
                $output->writeLn("<error>Module $sModule already activated.</error>");
            }
        }

    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function executeVersion480(InputInterface $input, OutputInterface $output)
    {
        $this->executeVersion470($input, $output);
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function executeVersion470(InputInterface $input, OutputInterface $output)
    {
        $sModule = $input->getArgument('module');

        $oModule = \oxNew('oxModule');

        if (!$oModule->load($sModule)) {
            $output->writeLn("<error>Cannot load module $sModule.</error>");
        }

        if (!$oModule->isActive()) {
            $output->writeLn("<error>Module $sModule already deactivated.</error>");
        } else {
            if ($oModule->deactivate() === true) {
                $output->writeLn("<info>Module $sModule deactivated.</info>");
            } else {
                $output->writeLn("<error>Module $sModule already activated.</error>");
            }
        }

    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }

}
