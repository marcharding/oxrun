<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 12.11.18
 * Time: 15:09
 */

namespace Oxrun\GenerateModule;


use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Metamorphose
{
    /**
     * @var string
     */
    private $modulePath;

    /**
     * Metamorphose constructor.
     * @param $modulePath
     */
    public function __construct($modulePath)
    {
        $this->modulePath = $modulePath;
    }

    /**
     * Create the Module ReadMe file
     */
    public function ReadMe()
    {
        $filesystem = new Filesystem();
        if (false == $filesystem->exists("{$this->modulePath}/README_MODULE.md")) {
            throw new IOException("Archive has\'t {$this->modulePath}/README_MODULE.md");
        }
        $filesystem->remove("{$this->modulePath}/README.md");
        $filesystem->rename("{$this->modulePath}/README_MODULE.md", "{$this->modulePath}/README.md");

        return $this;
    }

    /**
     * @param $search
     * @param $replace
     * @return $this
     */
    public function Replacement($search, $replace)
    {
        $moduleFiles = new Finder();
        $moduleFiles->files()->in($this->modulePath);

        /** @var SplFileInfo $file */
        foreach ($moduleFiles as $file) {
            $contents = $file->getContents();
            $contents = str_replace($search, $replace, $contents);
            file_put_contents($file->getPathname(), $contents);
        }

        return $this;
    }

}