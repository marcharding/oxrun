<?php

namespace Oxrun;

use Composer\Autoload\ClassLoader;
use Oxrun\Command\Custom;
use Oxrun\Helper\DatenbaseConnection;
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
     * @var DatenbaseConnection
     */
    protected $datenbaseConnection = null;

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
    protected function findBootstrapFile() {
        $input = new ArgvInput();
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
     * @param bool $skipViews Add 'blSkipViewUsage' to OXIDs config.
     * @return bool
     */
    public function checkBootstrapOxidInclude($oxBootstrap)
    {
        if (is_file($oxBootstrap)) {
            // is it the oxid bootstrap.php?
            if (strpos(file_get_contents($oxBootstrap), 'OX_BASE_PATH') !== false) {
                $this->shopDir = dirname($oxBootstrap);

                require_once $oxBootstrap;

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

            $datenbaseConnection = $this->getDatenbaseConnection();
            $datenbaseConnection
                ->setHost($oxConfigFile->getVar('dbHost'))
                ->setUser($oxConfigFile->getVar('dbUser'))
                ->setPass($oxConfigFile->getVar('dbPwd'))
                ->setDatabase($oxConfigFile->getVar('dbName'));

            return $this->hasDBConnection = $datenbaseConnection->canConnectToMysql();
        }

        return $this->hasDBConnection = false;
    }

    /**
     * @return DatenbaseConnection
     */
    public function getDatenbaseConnection()
    {
        if ($this->datenbaseConnection === null) {
            $this->datenbaseConnection = new DatenbaseConnection();
        }

        return $this->datenbaseConnection;
    }

}
