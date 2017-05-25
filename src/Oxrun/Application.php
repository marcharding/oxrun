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
     * @var ClassLoader|null
     */
    protected $autoloader;

    /**
     * @var string
     */
    protected $oxidConfigContent;

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
     * @return \Symfony\Component\Console\Input\InputDefinition
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
     * @param bool $skipViews Add 'blSkipViewUsage' to OXIDs config.
     * @return bool
     */
    public function bootstrapOxid($skipViews = false)
    {
        $input = new ArgvInput();
        if($input->getParameterOption('--shopDir')) {
            $oxBootstrap = $input->getParameterOption('--shopDir'). '/bootstrap.php';
            if( $this->checkBootstrapOxidInclude( $oxBootstrap, $skipViews ) === true ) {
                return true;
            }
            return false;
        }

        // try to guess where bootstrap.php is
        $currentWorkingDirectory = getcwd();
        do {
            $oxBootstrap = $currentWorkingDirectory . '/bootstrap.php';
            if( $this->checkBootstrapOxidInclude( $oxBootstrap, $skipViews ) === true ) {
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
     * @param bool $skipViews Add 'blSkipViewUsage' to OXIDs config.
     * @return bool
     */
    public function checkBootstrapOxidInclude($oxBootstrap, $skipViews = false)
    {
        if (is_file($oxBootstrap)) {
            // is it the oxid bootstrap.php?
            if (strpos(file_get_contents($oxBootstrap), 'OX_BASE_PATH') !== false) {
                $this->shopDir = dirname($oxBootstrap);

                if ($skipViews) {
                    $this->applyOxRunConfig(['blSkipViewUsage' => true]);
                }

                require_once $oxBootstrap;

                // If we've an autoloader we must re-register it to avoid conflicts with a composer autoloader from shop
                if (null !== $this->autoloader) {
                    $this->autoloader->unregister();
                    $this->autoloader->register(true);
                }

                // we must call this once, otherwise there are no modules visible in a fresh shop
                $oModuleList = oxNew("oxModuleList");
                $oModuleList->getModulesFromDir(\oxRegistry::getConfig()->getModulesDir());

                $this->removeOxRunConfig();

                return true;
            }
        }

        return false;
    }

    /**
     * Adds custom Oxrun configuration to config.inc.php (if exists and not already done).
     *
     * @param array $config
     */
    protected function applyOxRunConfig(array $config = [])
    {
        if (null === $this->oxidConfigContent) {
            $oxConfigInc    = "{$this->shopDir}/config.inc.php";
            $oxConfigExists = file_exists("{$this->shopDir}/config.inc.php");

            if ($oxConfigExists) {
                $this->oxidConfigContent = file_get_contents("{$this->shopDir}/config.inc.php");
                $newConfigContent = $this->oxidConfigContent;
                foreach ($config as $configKey => $configValue) {
                    $newConfigContent .= "\n\$this->{$configKey} = " . var_export($configValue, true);
                }

                file_put_contents($oxConfigInc, $newConfigContent);
            }

        }
    }

    /**
     * Removes custom Oxrun configuration from config.inc.php.
     */
    protected function removeOxRunConfig()
    {
        if (null !== $this->oxidConfigContent) {
            file_put_contents("{$this->shopDir}/config.inc.php", $this->oxidConfigContent);
        }
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
