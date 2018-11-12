<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 12.11.18
 * Time: 17:22
 */

namespace Oxrun\GenerateModule;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ComposerConfig
 *
 * @package Oxrun\GenerateModule
 */
class ComposerConfig
{
    public function addAutoload($shopRoot, $namespace, $path)
    {
        $composer_json = $shopRoot . '/../composer.json';

        $filesystem = new Filesystem();
        if (false == $filesystem->exists($composer_json)) {
            throw new FileNotFoundException("Composer.json not found " . $composer_json);
        }

        if (false == $filesystem->exists("$path")) {
            throw new FileNotFoundException("Module is not installed '$path'");
        }

        $relative_path = str_replace($shopRoot, './source', $path);

        $this->save($namespace, $relative_path, $composer_json);
    }

    /**
     * @param $namespace
     * @param $path
     * @param $composer_json
     */
    protected function save($namespace, $path, $composer_json)
    {
        $namespace = rtrim($namespace, '\\');
        $namespace .= '\\';

        $content = \GuzzleHttp\json_decode(file_get_contents($composer_json), true);

        $content['autoload']['psr-4'][$namespace] = $path;
        $content['autoload-dev']['psr-4'][$namespace . 'Tests\\'] = $path . '/tests';

        $json_encode = \GuzzleHttp\json_encode($content, JSON_PRETTY_PRINT);
        file_put_contents($composer_json, $json_encode);
    }
}