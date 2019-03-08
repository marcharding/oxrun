<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-04
 * Time: 21:04
 */

namespace Oxrun\CommandCollection;

use Symfony\Component\Config\Resource\FileResource;

/**
 * Class CacheCheck
 *
 * Save all command pfads so if anything change then will create a new container
 *
 * @package Oxrun\CommandCollection\Aggregator
 */
class CacheCheck
{
    /**
     * @var array
     */
    private static $path = [];

    /**
     * @param $path
     */
    public static function addFile($filepath)
    {
        self::$path[] = new FileResource($filepath);
    }

    /**
     * @return array
     */
    public static function getResource()
    {
        return self::$path;
    }

    public static function clean()
    {
        self::$path = [];
    }
}