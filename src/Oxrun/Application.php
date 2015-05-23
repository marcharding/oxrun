<?php

namespace Oxrun;

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
     * @var string
     */
    const APP_NAME = 'oxrun';

    /**
     * @var string
     */
    const APP_VERSION = '@package_version@';

    /**
     * @param \Composer\Autoload\ClassLoader $autoloader
     */
    public function __construct($autoloader = null)
    {
        $this->autoloader = $autoloader;
        parent::__construct(self::APP_NAME, self::APP_VERSION);
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
        $input->bind( $this->getDefinition() );
        if( $input->getOption('shopDir') ) {
            $oxBootstrap = $input->getOption('shopDir'). '/bootstrap.php';
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
     * @return Version tring
     */
    public function getOxidVersion()
    {
        $oxConfig = oxNew('oxConfig');
        $pkgInfo = parse_ini_file($oxConfig->getConfigParam('sShopDir') . 'pkg.info');
        return $pkgInfo['version'];
    }

}
