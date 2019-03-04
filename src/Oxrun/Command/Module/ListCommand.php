<?php

namespace Oxrun\Command\Module;

use Oxrun\Traits\ModuleListCheckTrait;
use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package Oxrun\Command\Module
 */
class ListCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;
    use ModuleListCheckTrait;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:list')
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
        $this->checkModulelist();

        $oxModuleList = oxNew(\OxidEsales\EshopCommunity\Core\Module\ModuleList::class);

        $activeModules = array_keys($oxModuleList->getActiveModuleInfo());
        $deactiveModules = $oxModuleList->getDisabledModules();;
        $activeModules = array_map(function ($item) {
            return array($item, 'yes');
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
}
