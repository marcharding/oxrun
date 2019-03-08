<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-07
 * Time: 07:50
 */

namespace Oxrun\CommandCollection;

/**
 * Class CommandFinder
 * Has all Commands inside
 * @package Oxrun\CommandCollection
 */
class CommandFinder
{
    /**
     * @var Aggregator[]
     */
    private $passNeedShopDir = [];

    /**
     * @var Aggregator[]
     */
    private $pass = [];

    /**
     * @param Aggregator $aggregator
     * @param bool $needShopDir
     *
     * @return static
     */
    public function addRegister(Aggregator $aggregator, $needShopDir = false)
    {
        if ($needShopDir) {
            $this->passNeedShopDir[] = $aggregator;
        } else {
            $this->pass[] = $aggregator;

        }

        return $this;
    }

    /**
     * @return Aggregator[]
     */
    public function getPassNeedShopDir()
    {
        return $this->passNeedShopDir;
    }

    /**
     * @return Aggregator[]
     */
    public function getPass()
    {
        return $this->pass;
    }


}
