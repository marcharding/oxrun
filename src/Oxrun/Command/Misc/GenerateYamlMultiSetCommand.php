<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-02
 * Time: 16:39
 */

namespace Oxrun\Command\Misc;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use Oxrun\Helper\MulitSetConfigConverter;
use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GenerateYamlMultiSetCommand
 * @package Oxrun\Command\Misc
 */
class GenerateYamlMultiSetCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;

    protected $ignoreVariablen = [
        'aDisabledModules',
        'aModuleControllers',
        'aModuleEvents',
        'aModuleExtensions',
        'aModuleFiles',
        'aModulePaths',
        'aModules',
        'aModuleTemplates',
        'aModuleVersions',
        'blModuleWasEnabled',
        'moduleSmartyPluginDirectories',
        'sUtilModule',
        'sDefaultLang',
        'aLanguages',
        'aLanguageURLs',
        'aLanguageParams',
        'IMA',
        'IMD',
        'IMS',
        'sCustomTheme',
        'sTheme',
    ];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('misc:generate:yaml:multiset')
            ->setDescription('Generate a Yaml File for command `config:multiset`');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yaml = ['config' => []];
        $config = Registry::getConfig();
        $shopIds = $config->getShopIds();

        foreach ($shopIds as $id) {
            $yaml['config'][$id] = $this->getConfigFromShop($id);
        }

        $path = $this->getSavePath();
        file_put_contents($path, Yaml::dump($yaml, 4, 5, Yaml::DUMP_OBJECT_AS_MAP));

        $output->writeln("<comment>Config saved. use `oxrun config:multiset shopConfigs.yml`</comment>");
    }

    /**
     * @param $shopId
     */
    protected function getConfigFromShop($shopId)
    {
        $decodeValueQuery = Registry::getConfig()->getDecodeValueQuery();
        $ignore = implode("', '", $this->ignoreVariablen);

        $SQL =  "SELECT oxvarname, oxvartype, {$decodeValueQuery} as oxvarvalue, oxmodule
                    FROM oxconfig
                    WHERE oxshopid = ?
                      AND NOT oxvarname IN ('$ignore')";

        $dbConf = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($SQL, [$shopId]);
        $yamlConf = [];

        $map = new MulitSetConfigConverter();
        array_map(function ($row) use (&$yamlConf, $map) {
            $converd = $map->convert($row);
            $yamlConf[$converd['key']] = $converd['value'];
        }, $dbConf);

        ksort($yamlConf);
        return $yamlConf;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        $oxrunConfigPath = $this->getApplication()->getOxrunConfigPath();
        return $oxrunConfigPath . 'shopConfigs.yml';
    }
}