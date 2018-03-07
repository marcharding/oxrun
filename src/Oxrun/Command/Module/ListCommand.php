<?php

namespace Oxrun\Command\Module;

use Oxrun\Traits\ModuleListCheckTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package Oxrun\Command\Module
 */
class ListCommand extends Command
{
    use ModuleListCheckTrait;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:list')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL, null)
            ->setDescription('Lists all modules');
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

        /* @var oxModuleList $oxModuleList  */
        $oxModuleList = oxNew('oxModuleList');

        $activeModules = array_keys($oxModuleList->getActiveModuleInfo());
        $deactiveModules = $oxModuleList->getDisabledModules();;
        $activeModules = array_map(function ($item) {
            // check if really active
            $oModule = oxNew('oxModule');
            if ($oModule->load($item) && $oModule->isActive()) {
                return array($item, 'yes');                
            }
            return array($item, 'no');
        }, $activeModules);

        // Fix for older oxid version < 4.9.0
        if (!is_array($deactiveModules)) {
            $deactiveModules = array();
        }

        $deactiveModules = array_map(function ($item) {
            return array($item, 'no');
        }, $deactiveModules);

        $table = new Table($output);
        $table
            ->setHeaders(array('Module', 'Active'))
            ->setRows(array_merge($activeModules, $deactiveModules));
        $table->render();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }

}
