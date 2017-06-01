<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <developer@tobimat.eu>
 */

namespace Oxrun\Helper;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Class ToolCache
 *
 * @package Oxrun\Helper
 */
class ToolCache implements CacheInterface
{
    /** @var FilesystemCache  */
    protected $filesystemCache = null;

    /**
     * ToolCache constructor.
     */
    public function __construct()
    {
        $this->filesystemCache = new FilesystemCache(
            'oxrun',
            date_create('+2 Week')->format('U')
        );
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->filesystemCache->get($key, $default);
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
        return $this->filesystemCache->clear();
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->filesystemCache->getMultiple($keys, $default);
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->filesystemCache->setMultiple($values, $ttl);
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function deleteMultiple($keys)
    {
        return $this->filesystemCache->deleteMultiple($keys);
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
