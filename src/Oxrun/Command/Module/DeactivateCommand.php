<?php

namespace Oxrun\Command\Module;

use Oxrun\Traits\ModuleListCheckTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL, null)
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
        $shopId = $input->getOption('shopId');
        if ($shopId) {
            $this->getApplication()->switchToShopId($shopId);
        }

        $this->checkModulelist($shopId);

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
        $shopId = $input->getOption('shopId');

        $oModule = \oxNew('oxModule');
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        $oModuleInstaller = oxNew('oxModuleInstaller', $oModuleCache);

        if (!$oModule->load($sModule)) {
            $output->writeLn("<error>Cannot load module $sModule.</error>");
        }

        if (!$oModule->isActive()) {
            $output->writeLn("<comment>Module $sModule already deactivated for shopId $shopId.</comment>");
        } else {
            if ($oModuleInstaller->deactivate($oModule) === true) {
                $output->writeLn("<info>Module $sModule deactivated for shopId $shopId.</info>");
            } else {
                $output->writeLn("<comment>Module $sModule already deactivated for shopId $shopId.</comment>");
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
        $shopId = $input->getOption('shopId');

        $oModule = \oxNew('oxModule');

        if (!$oModule->load($sModule)) {
            $output->writeLn("<error>Cannot load module $sModule.</error>");
        }

        if (!$oModule->isActive()) {
            $output->writeLn("<comment>Module $sModule already deactivated for shopId $shopId.</comment>");
        } else {
            if ($oModule->deactivate() === true) {
                $output->writeLn("<info>Module $sModule deactivated for shopId $shopId.</info>");
            } else {
                $output->writeLn("<comment>Module $sModule already deactivated for shopId $shopId.</comment>");
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
