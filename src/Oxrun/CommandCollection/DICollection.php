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
use Symfony\Component\Console\Command\Command;

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
     * @param Command $command
     * @param $foundPass
     */
    public function addFromDi($command, $foundPass)
    {
        if ($foundPass) {
            $aliases = $command->getAliases();
            $aliases[] = 'own:'.$foundPass.':'.$command->getName();
            $command->setAliases($aliases);
        }

        $this->commands[] = $command;
    }
}