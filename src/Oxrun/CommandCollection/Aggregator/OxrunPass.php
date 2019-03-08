<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:35
 */
namespace Oxrun\CommandCollection\Aggregator;

use Oxrun\CommandCollection\Aggregator;

/**
 * Class OxrunPass
 * @package Oxrun\CommandCollection\Aggregator
 */
class OxrunPass extends Aggregator
{
    /**
     * @inheritDoc
     */
    protected function getPassName()
    {
        return null;
    }

    /**
     * Oxrun Commands
     */
    protected function searchCommands()
    {
        $commandSourceDir          = __DIR__ . '/../../Command';
        $recursiveIteratorIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandSourceDir));
        $regexIterator             = new \RegexIterator($recursiveIteratorIterator, '/Command\.php$/');

        foreach ($regexIterator as $commandPath) {
            $commandClass = 'Oxrun\\Command';
            $commandClass .= str_replace(array($commandSourceDir, '/', '.php'), array('', '\\', ''), $commandPath);

            $this->add($commandClass);
        }
    }
}
