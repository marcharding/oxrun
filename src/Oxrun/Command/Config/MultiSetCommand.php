<?php

namespace Oxrun\Command\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MultiSetCommand
 * Can be used to set multiple oxconfig values for multiple subshops by providing a
 * YAML file containing the values per shop id. The format is e.g.
 * config:
 *   1:
 *     blReverseProxyActive: 
 *      variableType: bool
 *      variableValue: false
 *    # simple string type
 *    sMallShopURL: http://myshop.dev.local
 *    sMallSSLShopURL: http://myshop.dev.local
 *    myMultiVar:
 *      variableType: aarr
 *      variableValue:
 *        - /foo/bar/
 *        - /bar/foo/
 *      # optional module id
 *      moduleId: my_module
 *
 * Values without "variableType" are considered strings
 * @package Oxrun\Command\Config
 */
class MultiSetCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('config:multiset')
            ->setDescription('Sets multiple config values from yaml file')
            ->addArgument('configfile', InputArgument::REQUIRED, 'The yaml file name, relative to shop base.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // do not use the registry pattern (\oxRegistry::getConfig()) here, so we do not have any caches (breaks unit tests)
        $oxConfig = oxNew('oxConfig');

        /* @var \Oxrun\Application $app */
        $app = $this->getApplication();

        // now try to ready specified YAML file
        $mallYml = $input->getArgument('configfile');
        $ymlFile = $app->getShopDir() . DIRECTORY_SEPARATOR . $mallYml;
        if (!file_exists($ymlFile)) {
            $output->writeLn("<error>Yaml file '$ymlFile' not found!</error>");
            return;
        }
        $mallValues = Yaml::parse($ymlFile);
        if ($mallValues && is_array($mallValues['config'])) {
            $mallSettings = $mallValues['config'];
            foreach ($mallSettings as $shopId => $configData) {
                foreach ($configData as $configKey => $configValue) {
                    $moduleId = '';
                    if (!is_array($configValue)) {
                        // assume simple string
                        $variableType = 'str';
                        $variableValue = $configValue;
                    } else {
                        $variableType = $configValue['variableType'];
                        $variableValue = $configValue['variableValue'];
                        if (isset($configValue['moduleId'])) {
                            $moduleId = $configValue['moduleId'];
                        }
                    }
                    $oxConfig->saveShopConfVar(
                        $variableType,
                        $configKey,
                        $variableValue,
                        $shopId,
                        $moduleId
                    );
                    $output->writeln("<info>Config {$configKey} for shop {$shopId} set to " . print_r($variableValue, true) . "</info>");
                }
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