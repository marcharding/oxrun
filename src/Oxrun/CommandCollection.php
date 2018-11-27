<?php

namespace Oxrun;

/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:21
 */
interface CommandCollection
{
    public function addCommandTo(\Oxrun\Application $application);
}