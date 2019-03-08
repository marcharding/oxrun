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
     * @inheritDoc
     */
    protected function getPassName()
    {
        return 'oxrun';
    }

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
        $commandSourceDir          = $this->oxrunConfigDir . '/commands';
        if (!file_exists($commandSourceDir)) {
            return;
        }

        $recursiveIteratorIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandSourceDir));
        $regexIterator             = new \RegexIterator($recursiveIteratorIterator, '/.*Command\.php$/');

        /** @var \SplFileInfo $commandPath */
        foreach ($regexIterator as $commandPath) {
            $commandClass = 'Oxrun\\CustomCommand\\';
            $commandClass .= $commandPath->getBasename('.php');

            include_once $commandPath;

            $this->add($commandClass, $commandPath);
        }
    }
}
