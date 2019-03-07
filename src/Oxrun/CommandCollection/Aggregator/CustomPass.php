<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-07
 * Time: 07:29
 */

namespace Oxrun\CommandCollection\Aggregator;

use Oxrun\CommandCollection\Aggregator;

/**
 * Class CustomPass
 *
 * @package Oxrun\CommandCollection\Aggregator
 */
class CustomPass extends Aggregator
{
    /**
     * Algorithmus to find the Commands
     *
     * @return void
     */
    protected function searchCommands()
    {
        $this->addCustomCommandDir();
    }

    /**
     * Add custom command folder in OXID source directory
     *
     * @return void
     */
    public function addCustomCommandDir()
    {
        $commandSourceDir          = $this->oxrunConfigDir. '/commands';
        if (!file_exists($commandSourceDir)) {
            return;
        }

        $recursiveIteratorIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandSourceDir));
        $regexIterator             = new \RegexIterator($recursiveIteratorIterator, '/.*Command\.php$/');

        foreach ($regexIterator as $commandPath) {
            $commandClass = str_replace(array($commandSourceDir, '/', '.php'), array('', '\\', ''), $commandPath);
            if (!class_exists($commandClass)) {
                echo "\nClass $commandClass does not exist!\n";
                continue;
            }
            $this->add($commandClass, $commandPath);
        }
    }
}
