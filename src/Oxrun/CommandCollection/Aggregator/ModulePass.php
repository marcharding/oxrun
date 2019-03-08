<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-06
 * Time: 23:11
 */

namespace Oxrun\CommandCollection\Aggregator;

use Oxrun\CommandCollection\Aggregator;

/**
 * Class ModulePass
 * @package Oxrun\CommandCollection\Aggregator
 */
class ModulePass extends Aggregator
{
    /**
     * @inheritDoc
     */
    protected function getPassName()
    {
        return 'oxmodule';
    }

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
        $pathToPhpFiles = $this->getPathsOfCommands();

        foreach ($pathToPhpFiles as $phpFile) {
            $classesFromPhpFile = $this->getAllClassesFromPhpFile($phpFile);
            array_walk(
                $classesFromPhpFile,
                function ($class) use ($phpFile) {
                    $this->add($class, $phpFile);
                }
            );
        }

    }


    /**
     * Return list of all Command paths
     */
    private function getPathsOfCommands()
    {
        $moduleDir = $this->shopDir . '/modules';

        $recursiveIteratorIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($moduleDir, \FilesystemIterator::SKIP_DOTS));
        $regexIterator = new \RegexIterator($recursiveIteratorIterator, '~[^/]+/[^/]+/(Commands|commands|Command)/.+[cC]ommand\.php$~');

        return $regexIterator;
    }

    /**
     * Get list of defined classes from given PHP file.
     *
     * @param \SplFileInfo $pathToPhpFile
     *
     * @return string[]
     */
    private function getAllClassesFromPhpFile($pathToPhpFile)
    {
        $classesBefore = get_declared_classes();
        $filename = $pathToPhpFile->getBasename('.php');

        try {
            include_once $pathToPhpFile;

        } catch (\Throwable $exception) {
            $this->consoleOutput->writeln("<error>Can not add Command $pathToPhpFile:" . $exception->getMessage());
            return [];
        }

        $classesAfter = get_declared_classes();

        $newClasses = array_diff($classesAfter, $classesBefore);

        if (count($newClasses)) {
            //try to find the correct class name to use
            //this avoids warnings when module developer use there own command base class, that is not instantiable
            $quotefilename = preg_quote($filename);
            foreach ($newClasses as $newClass) {
                if (preg_match("/$quotefilename\$/i", $newClass)) {
                    return [$newClass];
                }
            }
        }

        $this->consoleOutput->writeln("<comment>Class '$filename' was not inside: " . $pathToPhpFile."</comment>");
        return [];
    }
}