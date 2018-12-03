<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 03.12.18
 * Time: 11:47
 */

namespace Oxrun\Traits;

/**
 * Class NeedDatabase
 * @package Oxrun\Traits
 */
trait NeedDatabase
{
    public function needDatabaseConnection()
    {
        return true;
    }
}
