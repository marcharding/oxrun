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
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('configfile', 'c', InputOption::VALUE_REQUIRED, 'The Config file to change or create if not exits', 'dev_config.yml')
            ->addOption('oxvarname', '', InputOption::VALUE_REQUIRED, 'Dump configs by oxvarname. One name or as comma separated List')
            ->addOption('oxmodule', '', InputOption::VALUE_REQUIRED, 'Dump configs by oxmodule. One name or as comma separated List')
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

        $path = $this->getSavePath($input);
        if (file_exists($path)) {
            $yaml = Yaml::parse(file_get_contents($path));
        }

        $config = Registry::getConfig();
        $shopIds = $config->getShopIds();

        if ($shopId = $input->getOption('shopId')) {
            $shopIds = [$shopId];
        }

        foreach ($shopIds as $id) {
            if (isset($yaml['config'][$id]) == false) {
                $yaml['config'][$id] = [];
            }

            $dbConfig = $this->getConfigFromShop($id, $input);
            $yaml['config'][$id] = array_merge($yaml['config'][$id], $dbConfig);
        }

        file_put_contents($path, Yaml::dump($yaml, 2, 4, Yaml::DUMP_OBJECT_AS_MAP));

        $output->writeln("<comment>Config saved. use `oxrun config:multiset ".$input->getOption('configfile')."`</comment>");
    }

    /**
     * @param $shopId
     */
    protected function getConfigFromShop($shopId, InputInterface $input)
    {
        $decodeValueQuery = Registry::getConfig()->getDecodeValueQuery();

        $SQL = "SELECT oxvarname, oxvartype, {$decodeValueQuery} as oxvarvalue, oxmodule
                    FROM oxconfig
                    WHERE oxshopid = ?";

        if ($option = $input->getOption('oxvarname')) {
            $SQL .= $this->andWhere('oxvarname', $option);
        } else {
            $ignore = implode("', '", $this->ignoreVariablen);
            $SQL .= " AND NOT oxvarname IN ('$ignore')";
        }

        if ($option = $input->getOption('oxmodule')) {
            $SQL .= $this->andWhere('oxmodule', $option);
        }

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
    public function getSavePath(InputInterface $input)
    {
        $filename = $input->getOption('configfile');
        $oxrunConfigPath = $this->getApplication()->getOxrunConfigPath();
        return $oxrunConfigPath . $filename;
    }

    /**
     * @param $column
     * @return string
     */
    protected function andWhere($column, $input)
    {
        $list = explode(',', $input);
        $list = array_map('trim', $list);
        $list = DatabaseProvider::getDb()->quoteArray($list);
        $list = implode(',', $list);

        return " AND $column IN ($list)";
    }
}