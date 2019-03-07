<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-06
 * Time: 23:11
 */

namespace Oxrun\CommandCollection\Aggregator;

use OxidEsales\Eshop\Core\Registry;
use Oxrun\CommandCollection\Aggregator;
use Oxrun\CommandCollection\CacheCheck;

/**
 * Class ModulePass
 * @package Oxrun\CommandCollection\Aggregator
 */
class ModulePass extends Aggregator
{
    /**
     * Algorithmus to find the Commands
     *
     * @return void
     */
    protected function searchCommands()
    {
        $this->addModulesCommandDirs();
    }

    /**
     * Add modules folder in OXID source directory
     * Every module may have a subfolder "[C|c]ommand[s]" containing
     * oxrun commands which we try to load here
     *
     * @return void
     */
    protected function addModulesCommandDirs()
    {
        $paths = $this->getPathsOfAvailableModules();

        $pathToPhpFiles = $this->getPhpFilesMatchingPatternForCommandFromGivenPaths($paths);

        $classes = $this->getAllClassesFromPhpFiles($pathToPhpFiles);

        foreach ($classes as $commandClass) {
            if (!class_exists($commandClass)) {
                echo "\nClass $commandClass does not exist!\n";
                continue;
            }

            $this->add($commandClass);
        }
    }


    /**
     * Return list of paths to all available modules.
     *
     * @return string[]
     */
    private function getPathsOfAvailableModules()
    {
        $config = Registry::getConfig();
        $modulesRootPath = $config->getModulesDir();

        $modulePaths = $config->getConfigParam('aModulePaths');

        if (!is_dir($modulesRootPath)) {
            return [];
        }
        if (!is_array($modulePaths)) {
            return [];
        }
        $fullModulePaths = array_map(function ($modulePath) use ($modulesRootPath) {
            return $modulesRootPath . $modulePath;
        }, array_values($modulePaths));
        return array_filter($fullModulePaths, function ($fullModulePath) {
            return is_dir($fullModulePath);
        });
    }
    /**
     * Return list of PHP files matching `Command` specific pattern.
     *
     * @param string $path Path to collect files from
     *
     * @return string[]
     */
    private function getPhpFilesMatchingPatternForCommandFromGivenPath($path)
    {
        $folders = ['Commands','commands','Command'];

        foreach ($folders as $f) {
            $cPath = $path . DIRECTORY_SEPARATOR . $f . DIRECTORY_SEPARATOR;
            if (!is_dir($cPath)) {
                continue;
            }

            $files = glob("$cPath*[cC]ommand\.php");

            return $files;
        }

        return [];
    }

    /**
     * Helper method for `getPhpFilesMatchingPatternForCommandFromGivenPath`
     *
     * @param string[] $paths
     *
     * @return string[]
     */
    private function getPhpFilesMatchingPatternForCommandFromGivenPaths($paths)
    {
        return $this->getFlatArray(
            array_map(
                function ($path) {
                    return $this->getPhpFilesMatchingPatternForCommandFromGivenPath($path);
                },
                $paths
            )
        );
    }

    /**
     * Helper method for `getAllClassesFromPhpFile`
     *
     * @param string[] $pathToPhpFiles
     *
     * @return string[]
     */
    private function getAllClassesFromPhpFiles($pathToPhpFiles)
    {
        return $this->getFlatArray(
            array_map(
                function ($pathToPhpFile) {
                    return $this->getAllClassesFromPhpFile($pathToPhpFile);
                },
                $pathToPhpFiles
            )
        );
    }

    /**
     * Get list of defined classes from given PHP file.
     *
     * @param string $pathToPhpFile
     *
     * @return string[]
     */
    private function getAllClassesFromPhpFile($pathToPhpFile)
    {
        $classesBefore = get_declared_classes();

        try {

            CacheCheck::addFile($pathToPhpFile);
            include_once $pathToPhpFile;

        } catch (\Throwable $exception) {
            print "Can not add Command $pathToPhpFile:\n";
            print $exception->getMessage() . "\n";
        }

        $classesAfter = get_declared_classes();

        $newClasses = array_diff($classesAfter, $classesBefore);

        if (count($newClasses) > 1) {
            //try to find the correct class name to use
            //this avoids warnings when module developer use there own command base class, that is not instantiable
            $name = basename($pathToPhpFile, '.php');

            foreach ($newClasses as $newClass) {
                if ($newClass == $name) {
                    return [$newClass];
                }
            }
        }

        return $newClasses;
    }

    /**
     * Convert array of arrays to flat list array.
     *
     * @param array[] $nonFlatArray
     *
     * @return array
     */
    private function getFlatArray($nonFlatArray)
    {
        return array_reduce($nonFlatArray, 'array_merge', []);
    }
}