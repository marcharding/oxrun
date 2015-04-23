<?php

namespace Oxrun;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;

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
     * @return bool
     */
    public function bootstrapOxid()
    {
        // try to guess where bootstrap.php is
        $currentWorkingDirectory = getcwd();
        do {
            $oxBootstrap = $currentWorkingDirectory . '/bootstrap.php';
            if (is_file($oxBootstrap)) {
                // is it the oxid bootstrap.php?
                if (strpos(file_get_contents($oxBootstrap), 'OX_BASE_PATH') !== false) {
                    require_once $oxBootstrap;
                    return true;
                    break;
                }
            }
            $currentWorkingDirectory = dirname($currentWorkingDirectory);
        } while ($currentWorkingDirectory !== '/');
        return false;
    }

    public function getOxidVersion()
    {
        $oxConfig = oxNew('oxConfig');
        $pkgInfo = parse_ini_file($oxConfig->getConfigParam('sShopDir') . 'pkg.info');
        return $pkgInfo['version'];
    }

}
