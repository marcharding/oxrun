<?php

namespace Oxrun\CommandCollection;

use Oxrun\CommandCollection;

/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:35
 */
class OxrunCollection implements CommandCollection
{
    /**
     * @param \Oxrun\Application $application
     */
    public function addCommandTo(\Oxrun\Application $application)
    {
        $commandSourceDir          = __DIR__ . '/../Command';
        $recursiveIteratorIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandSourceDir));
        $regexIterator             = new \RegexIterator($recursiveIteratorIterator, '/.*Command\.php$/');

        foreach ($regexIterator as $commandPath) {
            $commandClass = '\\Oxrun\\Command';
            $commandClass .= str_replace(array($commandSourceDir, '/', '.php'), array('', '\\', ''), $commandPath);
            $application->add(new $commandClass);
        }
    }
}
