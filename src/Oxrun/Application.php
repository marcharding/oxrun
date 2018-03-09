<?php

namespace Oxrun;

use Composer\Autoload\ClassLoader;
use Oxrun\Command\Custom;
use Oxrun\Helper\DatabaseConnection;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Application
 * @package Oxrun
 */
class Application extends BaseApplication
{
    /**
     * @var null
     */
    protected $oxidBootstrapExists = null;

    /**
     * @var null
     */
    protected $hasDBConnection = null;

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
     * @var databaseConnection
     */
    protected $databaseConnection = null;

    /**
     * @var string
     */
    protected $oxid_version = "0.0.0";

    /**
     * @param ClassLoader   $autoloader The composer autoloader
     * @param string        $name
     * @param string        $version
     */
    public function __construct($autoloader = null, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->autoloader = $autoloader;
        parent::__construct($name, $version);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultCommands()
    {
        return array(new HelpCommand(), new Custom\ListCommand());
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
     * Oxid bootstrap.php is loaded.
     *
     * @param bool $blNeedDBConnection this Command need a DB Connection
     *
     * @return bool|null
     */
    public function bootstrapOxid($blNeedDBConnection = true)
    {
        if ($this->oxidBootstrapExists === null) {
            $this->oxidBootstrapExists = $this->findBootstrapFile();
        }

        if ($this->oxidBootstrapExists && $blNeedDBConnection) {
            return $this->canConnectToDB();
        }

        return $this->oxidBootstrapExists;
    }

    /**
     * Search Oxid Bootstrap.file and include that
     *
     * @return bool
     */
    protected function findBootstrapFile()
    {
        $input = new ArgvInput();
        if ($input->getParameterOption('--shopDir')) {
            $oxBootstrap = $input->getParameterOption('--shopDir'). '/bootstrap.php';
            if ($this->checkBootstrapOxidInclude($oxBootstrap) === true ) {
                return true;
            }
            return false;
        }

        // try to guess where bootstrap.php is
        $currentWorkingDirectory = getcwd();
        do {
            $oxBootstrap = $currentWorkingDirectory . '/bootstrap.php';
            if ($this->checkBootstrapOxidInclude($oxBootstrap) === true ) {
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
     * @param bool   $skipViews   Add 'blSkipViewUsage' to OXIDs config.
     * 
     * @return bool
     */
    public function checkBootstrapOxidInclude($oxBootstrap)
    {
        if (is_file($oxBootstrap)) {
            // is it the oxid bootstrap.php?
            if (strpos(file_get_contents($oxBootstrap), 'OX_BASE_PATH') !== false) {
                $this->shopDir = dirname($oxBootstrap);

                include_once $oxBootstrap;

                // If we've an autoloader we must re-register it to avoid conflicts with a composer autoloader from shop
                if (null !== $this->autoloader) {
                    $this->autoloader->unregister();
                    $this->autoloader->register(true);
                }

                return true;
            }
        }

        return false;
    }


    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getOxidVersion()
    {
        if ($this->oxid_version != '0.0.0') {
            return $this->oxid_version;
        }

        if ($this->findVersionOnOxidLegacy() == false) {
            $this->findVersionOnOxid6();
        }

        return $this->oxid_version;
    }

    /**
     * @return string
     */
    public function getShopDir()
    {
        return $this->shopDir;
    }

    /**
     * @return bool
     */
    public function canConnectToDB()
    {
        if ($this->hasDBConnection !== null) {
            return $this->hasDBConnection;
        }

        $configfile = $this->shopDir . DIRECTORY_SEPARATOR . 'config.inc.php';

        if ($this->shopDir && file_exists($configfile)) {
            $oxConfigFile = new \OxConfigFile($configfile);

            $databaseConnection = $this->getDatabaseConnection();
            $databaseConnection
                ->setHost($oxConfigFile->getVar('dbHost'))
                ->setUser($oxConfigFile->getVar('dbUser'))
                ->setPass($oxConfigFile->getVar('dbPwd'))
                ->setDatabase($oxConfigFile->getVar('dbName'));

            return $this->hasDBConnection = $databaseConnection->canConnectToMysql();
        }

        return $this->hasDBConnection = false;
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection()
    {
        if ($this->databaseConnection === null) {
            $this->databaseConnection = new DatabaseConnection();
        }

        return $this->databaseConnection;
    }

    /**
     * Completely switch shop
     *
     * @param string $shopId The shop id
     * 
     * @return void
     */
    public function switchToShopId($shopId)
    {
        $oxidVersion = $this->getOxidVersion();
        if (version_compare($oxidVersion, '4.9.0') < 0) {
            // old OXID versions
            $oConfig = \oxRegistry::getConfig();
            $oConfig->setShopId($shopId);
            \oxRegistry::set('oxConfig', $oConfig);
            return;
        }

        $_POST['shp'] = $shopId;
        $_POST['actshop'] = $shopId;
        
        $keepThese = [\OxidEsales\Eshop\Core\ConfigFile::class];
        $registryKeys = \OxidEsales\Eshop\Core\Registry::getKeys();
        foreach ($registryKeys as $key) {
            if (in_array($key, $keepThese)) {
                continue;
            }
            \OxidEsales\Eshop\Core\Registry::set($key, null);
        }

        $utilsObject = new \OxidEsales\Eshop\Core\UtilsObject;
        $utilsObject->resetInstanceCache();
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsObject::class, $utilsObject);

        \OxidEsales\Eshop\Core\Module\ModuleVariablesLocator::resetModuleVariables();
        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('shp', $shopId);

        //ensure we get rid of all instances of config, even the one in Core\Base
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, null);
        \OxidEsales\Eshop\Core\Registry::getConfig()->setConfig(null);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, null);

        $moduleVariablesCache = new \OxidEsales\Eshop\Core\FileCache();
        $shopIdCalculator = new \OxidEsales\Eshop\Core\ShopIdCalculator($moduleVariablesCache);

        if (($shopId != $shopIdCalculator->getShopId()) 
            || ($shopId != \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId())
        ) {
            throw new \Exception('Failed to switch to subshop id ' . $shopId . " Calculate ID: " . $shopIdCalculator->getShopId() . " Config ShopId: " . \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId());
        }
    }
    
    /**
     * Find Version on Place into Oxid Legacy Code
     *
     * @return bool
     */
    protected function findVersionOnOxidLegacy()
    {
        $pkgInfo = $this->getShopDir() . DIRECTORY_SEPARATOR . 'pkg.info';
        if (file_exists($pkgInfo)) {
            $pkgInfo = parse_ini_file($pkgInfo);
            $this->oxid_version = $pkgInfo['version'];
            return true;
        }
        return false;
    }

    /**
     * Find Version up to OXID 6 Version
     * @throws \Exception
     */
    protected function findVersionOnOxid6()
    {
        if (!class_exists('OxidEsales\\Eshop\\Core\\ShopVersion')) {
            throw new \Exception('Can\'t find Shop Version. Maybe run OXID `Unified Namespace Generator` with composer');
        }

        $this->oxid_version = \OxidEsales\Eshop\Core\ShopVersion::getVersion();
    }
}
