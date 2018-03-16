<?php
/**
 * Created for oxrun
 * Author: Stefan Moises <moises@shoptimax.de>
 * Date: 07.03.18
 * Time: 08:46
 */

namespace Oxrun\Command\Module;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MultiActivateCommand
 * 
 * @package Oxrun\Command\Module
 * 
 * This command can be used to activate multiple modules for multiple shops.
 * You need to pass it a valid yaml file as argument, relative to the shop root dir,
 * containing either a "whitelist" or a
 * "blacklist" with shop ids and module ids, e.g.
 * whitelist:
 *   1: 
 *     - oepaypal
 *     - oxpspaymorrow
 *   2:
 *     - oepaypal
 *     - oxpspaymorrow
 */
class MultiActivateCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:multiactivate')
            ->setDescription('Activate multiple modules')
            ->addOption('shopId', null, InputOption::VALUE_REQUIRED, "The shop id.")
            ->addOption('skipDeactivation', 's', InputOption::VALUE_NONE, "Skip deactivation of modules, only activate.")
            ->addOption('skipClear', 'c', InputOption::VALUE_NONE, "Skip cache clearing.")
            ->addArgument('module', InputArgument::REQUIRED, 'YAML module list filename');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $activateShopId = $input->getOption('shopId');
        /* @var \Oxrun\Application $app */
        $app = $this->getApplication();
        $skipDeactivation = $input->getOption('skipDeactivation');
        $skipClear = $input->getOption('skipClear');

        // now try to ready specified YAML file
        $moduleYml = $input->getArgument('module');
        $ymlFile = $app->getShopDir() . DIRECTORY_SEPARATOR . $moduleYml;
        if (!file_exists($ymlFile)) {
            $output->writeLn("<error>Yaml file '$ymlFile' not found!</error>");
            return;
        }
        $moduleValues = Yaml::parse($ymlFile);

        if ($moduleValues && is_array($moduleValues)) {
            // use whitelist
            if (isset($moduleValues['whitelist'])) {
                foreach ($moduleValues['whitelist'] as $shopId => $moduleIds) {
                    if ($activateShopId && $activateShopId != $shopId) {
                        $output->writeLn("<comment>Skipping shop '$shopId'!</comment>");
                        continue;
                    }
                    foreach ($moduleIds as $moduleId) {
                        if (!$skipDeactivation) {
                            $arguments = array(
                                'command' => 'module:deactivate',
                                'module'    => $moduleId,
                                '--shopId'  => $shopId,
                            );              
                            $deactivateInput = new ArrayInput($arguments);          
                            $app->find('module:deactivate')->run($deactivateInput, $output);
                            if (!$skipClear) {
                                $app->find('cache:clear')->run(new ArgvInput([]), $output);
                            }
                        }
                        $arguments = array(
                            'command' => 'module:activate',
                            'module'    => $moduleId,
                            '--shopId'  => $shopId,
                        );              
                        $activateInput = new ArrayInput($arguments);          
                        $app->find('module:activate')->run($activateInput, $output);
                    }
                }
            } elseif (isset($moduleValues['blacklist'])) {
                // use blacklist
                /* @var \OxidEsales\Eshop\Core\Module\ModuleList $oxModuleList  */
                $oxModuleList = oxNew(\OxidEsales\Eshop\Core\Module\ModuleList::class);
                $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
                $aModules = $oxModuleList->getModulesFromDir($oConfig->getModulesDir());
                foreach ($aModules as $moduleId => $aModuleData) {
                    foreach ($moduleValues['blacklist'] as $shopId => $moduleIds) {
                        if (in_array($moduleId, $moduleIds)) {
                            $output->writeLn("<comment>Module blacklisted: '$moduleId' - skipping!</comment>");
                            continue 2;
                        }
                        // activate
                        if (!$skipDeactivation) {
                            $arguments = array(
                                'command' => 'module:deactivate',
                                'module'    => $moduleId,
                                '--shopId'  => $shopId,
                            );              
                            $deactivateInput = new ArrayInput($arguments);          
                            $app->find('module:deactivate')->run($deactivateInput, $output);
                            if (!$skipClear) {
                                $app->find('cache:clear')->run(new ArgvInput([]), $output);
                            }
                        }
                        $arguments = array(
                            'command' => 'module:activate',
                            'module'    => $moduleId,
                            '--shopId'  => $shopId,
                        );              
                        $activateInput = new ArrayInput($arguments);          
                        $app->find('module:activate')->run($activateInput, $output);
                    }
                }
            } else {
                $output->writeLn("<comment>No modules to activate for subshop '$shopId'!</comment>");
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
