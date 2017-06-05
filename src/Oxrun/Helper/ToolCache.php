<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <developer@tobimat.eu>
 */

namespace Oxrun\Helper;

use Desarrolla2\Cache\Adapter\File;
use Desarrolla2\Cache\Cache;
use Psr\SimpleCache\CacheInterface;

/**
 * Class ToolCache
 *
 * @package Oxrun\Helper
 */
class ToolCache implements CacheInterface
{

    const TOWWEEKS = 1209600; #sec 60 * 60 * 24 * 14;
    const ONEYEAR  = 31557600; #sec 60 * 60 * 24 * 365;

    /**
     * @var Cache
     */
    protected $filesystemCache = null;

    /**
     * ToolCache constructor.
     */
    public function __construct()
    {

        $fileAdapter = new File(sys_get_temp_dir()."oxrun_cache");
        $fileAdapter->setOption('ttl', self::TOWWEEKS);

        $this->filesystemCache = new Cache($fileAdapter);
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->filesystemCache->get($key);

        return $value ? $value : $default;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->filesystemCache->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function delete($key)
    {
        return $this->filesystemCache->delete($key);
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function clear()
    {
        $this->filesystemCache->dropCache();
        return true;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function setMultiple($values, $ttl = null)
    {
        if (is_array($values) == false) {
            throw new \InvalidArgumentException("values must be a array");
        }

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function deleteMultiple($keys)
    {
        if (is_array($keys) == false) {
            throw new \InvalidArgumentException("values must be a array");
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function has($key)
    {
        return $this->filesystemCache->has($key);
    }
}
