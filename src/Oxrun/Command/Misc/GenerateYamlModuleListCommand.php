<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-03
 * Time: 23:17
 */

namespace Oxrun\Command\Misc;

use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Registry;
use Oxrun\Command\EnableInterface;
use Oxrun\Traits\ModuleListCheckTrait;
use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GenerateYamlModuleListCommand
 * @package Oxrun\Command\Misc
 */
class GenerateYamlModuleListCommand extends Command implements EnableInterface
{
    use NeedDatabase;
    use ModuleListCheckTrait;

    /**
     * Configure Command
     */
    protected function configure()
    {
        $this
            ->setName('misc:generate:yaml:module')
            ->addOption('configfile', 'c', InputOption::VALUE_REQUIRED, 'The Config file to change or create if not exits', 'dev_module.yml')
            ->addOption('whitelist', 'w', InputOption::VALUE_NONE, 'Takes modules that are always activated. All others remain deactive.')
            ->addOption('blacklist', 'b', InputOption::VALUE_NONE, 'Takes modules that always need to be disabled. All others are activated.')
            ->setDescription('Generate a Yaml File for command `module:multiactivate`');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getSavePath($input);
        if (file_exists($path)) {
            $yaml = Yaml::parse(file_get_contents($path));
        }

        $config = Registry::getConfig();
        $shopIds = $config->getShopIds();

        if ($shopId = $input->getOption('shopId')) {
            $shopIds = [$shopId];
        }

        $listtype = $input->getOption('blacklist') ? 'blacklist' : 'whitelist';
        if (!isset($yaml[$listtype])) {
            $yaml[$listtype] = [];
        }

        foreach ($shopIds as $id) {
            if (!isset($yaml[$listtype][$id])) {
                $yaml[$listtype][$id] = [];
            }
            if ($input->getOption('blacklist')) {
                $modules = $this->getDeactiveModules($id);
            } else {
                $modules = $this->getActiveModules($id);
            }
            $yaml[$listtype][$id] = $modules;
        }

        if ($input->getOption('whitelist') && isset($yaml['blacklist'])) {
            unset($yaml['blacklist']);
        }
        if ($input->getOption('blacklist') && isset($yaml['whitelist'])) {
            unset($yaml['whitelist']);
        }

        file_put_contents($path, Yaml::dump($yaml, 3, 2));

        $output->writeln("<comment>Module saved use `oxrun module:multiactivate ".basename($path)."`</comment>");
    }

    /**
     * @param integer $shopId
     *
     * @return array
     */
    protected function getActiveModules($shopId)
    {
        $this->checkModulelist($shopId);
        $oxModuleList = Registry::get(ModuleList::class);

        return $activeModules = array_keys($oxModuleList->getActiveModuleInfo());
    }

    /**
     * @param integer $shopId
     *
     * @return array
     */
    protected function getDeactiveModules($shopId)
    {
        $this->checkModulelist($shopId);
        $oxModuleList = Registry::get(ModuleList::class);

        return $deactiveModules = $oxModuleList->getDisabledModules();
    }

    /**
     * @return string
     */
    public function getSavePath(InputInterface $input)
    {
        $filename = $input->getOption('configfile');
        $oxrunConfigPath = $this->getApplication()->getOxrunConfigPath();
        if (false == preg_match('/\.ya?ml$/', $filename)) {
            $filename .= '.yml';
        }
        return $oxrunConfigPath . $filename;
    }
}
