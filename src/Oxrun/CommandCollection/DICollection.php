<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-04
 * Time: 19:15
 */

namespace Oxrun\CommandCollection;

use Oxrun\Application;
use Oxrun\CommandCollection;

/**
 * Class DICollection
 * Only for Symfony DI Componente
 *
 * @package Oxrun\CommandCollection
 */
class DICollection implements CommandCollection
{
    /**
     * @var array
     */
    private $commands = [];

    /**
     * @param Application $application
     */
    public function addCommandTo(Application $application)
    {
        foreach ($this->commands as $command) {
            $application->add(new EnableAdapter($command));
        }
    }

    /**
     * @param $command
     */
    public function addFromDi($command)
    {
        $this->commands[] = $command;
    }
}