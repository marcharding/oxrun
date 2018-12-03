<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 03.12.18
 * Time: 11:49
 */

namespace Oxrun\Traits;


trait NoNeedDatabase
{
    public function needDatabaseConnection()
    {
        return false;
    }
}
