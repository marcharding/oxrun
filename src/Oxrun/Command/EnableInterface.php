<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-12-03
 * Time: 00:04
 */

namespace Oxrun\Command;

/**
 * Interface EnableInterface
 * @package Oxrun\Command
 */
interface EnableInterface
{
    public function needDatabaseConnection();
}