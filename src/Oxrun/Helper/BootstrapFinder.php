<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 2019-05-03
 * Time: 11:30
 */

namespace Oxrun\Helper;

use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class BootstrapFinder
 * @package Oxrun\Helper
 */
class BootstrapFinder
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private $autoloader;

    /**
     * @var string
     */
    private $shopDir;

    /**
     * @inheritDoc
     */
    public function __construct($autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * @return bool
     */
    public function isFound()
    {
        $findByArgument = $this->findByArgument();
        if ($findByArgument !== null) {
            return $findByArgument;
        }

        $findByEnv = $this->findByEnvironmentVariable();
        if ($findByEnv !== null) {
            return $findByEnv;
        }

        $findAsPackage = $this->findAsOxidPackage();
        if ($findAsPackage !== null) {
            return $findAsPackage;
        }

        return $this->searchingBootstrap(getcwd());
    }

    /**
     * Prüft anhand des --shopDir Argument ob der Shop vorhanden ist
     *
     * Wenn der Rückgabe wert null ist dann wurde kein Argument angegeben.
     *
     * @return bool|null
     */
    public function findByArgument()
    {
        $input = new ArgvInput();
        if ($input->getParameterOption('--shopDir') == '') {
            return null;
        }

        //start from oxid install root folder.
        $oxBootstrap = $input->getParameterOption('--shopDir') . '/source/bootstrap.php';
        if ($this->checkBootstrapAndInclude($oxBootstrap) === true) {
            return true;
        }

        //maybe is source folder.
        $oxBootstrap = $input->getParameterOption('--shopDir') . '/bootstrap.php';
        if ($this->checkBootstrapAndInclude($oxBootstrap) === true) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|null
     */
    protected function findByEnvironmentVariable()
    {
        $oxid_dir = getenv('OXID_SHOP_DIR');

        if ($oxid_dir == '') {
            return null;
        }

        return $this->searchingBootstrap($oxid_dir);
    }

    /**
     * If oxrun installed in the same composer package as oxid eshop.
     * So the path to bootstrap.php is logic.
     *
     * @return bool|null
     */
    public function findAsOxidPackage()
    {
        if (\Phar::running() != '') {
            return null; // oxrun is a phar script
        }

        //hard core place              /vendor/oxidprojects/oxrun/src/Oxrun/Helper/./source/bootstrap.php
        $standardOxidPath = __DIR__ . '/../../../../../../source/bootstrap.php';

        if (!is_file($standardOxidPath)) {
            return null;
        }

        return $this->checkBootstrapAndInclude($standardOxidPath);
    }

    /**
     * Check if bootstrap file exists
     *
     * @param String $oxBootstrap Path to oxid bootstrap.php
     *
     * @return bool
     */
    protected function checkBootstrapAndInclude($oxBootstrap)
    {
        if (is_file($oxBootstrap) == false) {
            return false;
        }

        // is that oxid bootstrap.php file?
        if (strpos(file_get_contents($oxBootstrap), 'OX_BASE_PATH') === false) {
            return false;
        }

        $this->shopDir = dirname($oxBootstrap);
        $realPath = (new \SplFileInfo($this->shopDir))->getRealPath();
        if ($realPath) {
            $this->shopDir = $realPath;
        }

        include_once $oxBootstrap;

        // If we've an autoloader we must re-register it to avoid conflicts with a composer autoloader from shop
        if (null !== $this->autoloader) {
            $this->autoloader->unregister();
            $this->autoloader->register(true);
        }

        return true;
    }

    /**
     * Search in Folder and backwards
     * @return bool
     */
    protected function searchingBootstrap($currentWorkingDirectory)
    {
        //start from oxid install root folder.
        $oxBootstrap = $currentWorkingDirectory . '/source/bootstrap.php';
        if ($this->checkBootstrapAndInclude($oxBootstrap) === true) {
            return true;
        }

        //try backwards to find bootstrap.
        do {
            $oxBootstrap = $currentWorkingDirectory . '/bootstrap.php';
            if ($this->checkBootstrapAndInclude($oxBootstrap) === true) {
                return true;
            }
            $currentWorkingDirectory = dirname($currentWorkingDirectory);
        } while ($currentWorkingDirectory !== '/');

        return false;
    }

    /**
     * @return string
     */
    public function getShopDir()
    {
        return $this->shopDir;
    }
}
