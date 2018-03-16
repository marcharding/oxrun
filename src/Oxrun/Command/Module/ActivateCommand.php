<?php

namespace Oxrun\Command\Module;

use Oxrun\Traits\ModuleListCheckTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ActivateCommand
 * 
 * @package Oxrun\Command\Module
 * 
 */
class ActivateCommand extends Command
{
    use ModuleListCheckTrait;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:activate')
            ->setDescription('Activates a module')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL, null);
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

        $this->executeActivate($input, $output);
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function executeActivate(InputInterface $input, OutputInterface $output)
    {
        $sModule = $input->getArgument('module');
        $shopId = $input->getOption('shopId');

        $oModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
        $oModuleCache = oxNew(\OxidEsales\Eshop\Core\Module\ModuleCache::class, $oModule);
        $oModuleInstaller = oxNew(\OxidEsales\Eshop\Core\Module\ModuleInstaller::class, $oModuleCache);

        if (!$oModule->load($sModule)) {
            $output->writeLn("<error>Cannot load module $sModule.</error>");
        }

        if (!$oModule->isActive()) {
            try {
                if ($oModuleInstaller->activate($oModule) === true) {
                    $output->writeLn("<info>Module $sModule activated for shopId $shopId.</info>");
                } else {
                    $output->writeLn("<error>Module $sModule could not be activated for shopId $shopId.</error>");
                }    
            } catch (\Exception $ex) {
                $output->writeLn("<error>Exception actiating module: $sModule for shop $shopId: {$ex->getMessage()}</error>");
            }
        } else {
            $output->writeLn("<comment>Module $sModule already activated for shopId $shopId.</comment>");
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
