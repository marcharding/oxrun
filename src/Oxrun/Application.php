<?php

namespace Oxrun;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Application
 * @package Oxrun
 */
class Application extends BaseApplication
{
    /**
     * Oxid eshop shop dir
     *
     * @var string
     */
    protected $shopDir;

    /**
     * @param ClassLoader   $autoloader
     * @param string $name
     * @param string $version
     */
    public function __construct($autoloader = null, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->autoloader = $autoloader;
        parent::__construct($name, $version);
    }

    /**
     * @return \Symfony\Component\Console\Input\InputDefinition|void
     */
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $shopDirOption = new InputOption(
            '--shopDir',
            '',
            InputOption::VALUE_OPTIONAL,
            'Force oxid base dir. No auto detection'
        );
        $inputDefinition->addOption($shopDirOption);

        return $inputDefinition;
    }

    /**
     * @return bool
     */
    public function bootstrapOxid()
    {
        $input = new ArgvInput;
        if($input->getParameterOption('--shopDir')) {
            $oxBootstrap = $input->getParameterOption('--shopDir'). '/bootstrap.php';
            if( $this->checkBootstrapOxidInclude( $oxBootstrap ) === true ) {
                return true;
            }
            return false;
        }

        // try to guess where bootstrap.php is
        $currentWorkingDirectory = getcwd();
        do {
            $oxBootstrap = $currentWorkingDirectory . '/bootstrap.php';
            if( $this->checkBootstrapOxidInclude( $oxBootstrap ) === true ) {
                return true;
                break;
            }
            $currentWorkingDirectory = dirname($currentWorkingDirectory);
        } while ($currentWorkingDirectory !== '/');
        return false;
    }

    /**
     * Check if bootstrap file exists
     *
     * @param String $oxBootstrap Path to oxid bootstrap.php
     * @return bool
     */
    public function checkBootstrapOxidInclude($oxBootstrap)
    {
        if (is_file($oxBootstrap)) {
            // is it the oxid bootstrap.php?
            if (strpos(file_get_contents($oxBootstrap), 'OX_BASE_PATH') !== false) {
                $this->shopDir = dirname($oxBootstrap);
                require_once $oxBootstrap;
                // we must call this once, otherwise there are no modules visible in a fresh shop
                $oModuleList = oxNew("oxModuleList");
                $oModuleList->getModulesFromDir(\oxRegistry::getConfig()->getModulesDir());
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getOxidVersion()
    {
        $oxConfig = oxNew('oxConfig');
        $pkgInfo = parse_ini_file($oxConfig->getConfigParam('sShopDir') . 'pkg.info');
        return $pkgInfo['version'];
    }

    /**
     * @return string
     */
    public function getShopDir()
    {
        return $this->shopDir;
    }

}
