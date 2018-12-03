<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-30
 * Time: 15:54
 */

namespace Oxrun\CommandCollection;


use Oxrun\Command\EnableInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class EnableAdapter
 *
 * An adapter to keep all commands compatible with other tools
 *
 * @package Oxrun\CommandCollection
 */
class EnableAdapter
{
    /**
     * @var Command|EnableInterface
     */
    private $command;

    /**
     * EnableAdapter constructor.
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        call_user_func_array([$this->command, $name], $arguments);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $dbconnect = false;

        if ($this->command instanceof \Oxrun\Command\EnableInterface) {
            $dbconnect = $this->command->needDatabaseConnection();
        } elseif (false == $this->command->isEnabled()) {
            return false;
        }

        return $this->command->getApplication()->bootstrapOxid($dbconnect);
    }
}