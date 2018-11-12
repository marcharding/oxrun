<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 09.11.18
 * Time: 21:06
 */

namespace Oxrun\GenerateModule;

/**
 * Class ModuleSpecification
 * @package Oxrun\GenerateModule
 */
class ModuleSpecification
{
    /**
     * Has all placeholder
     *
     * @var array
     */
    protected $replacement = [];

    /**
     * @var string
     */
    private $normalizerModuleName;


    /**
     * @var bool
     */
    private $initReplacement = false;

    /**
     * @param $moduleName
     * @return $this
     */
    public function setModuleName($moduleName)
    {
        $normalizer = new Normalizer();

        $this->normalizerModuleName = $normalizer->moduleName($moduleName);
        $this->replacement['MODULE_NAME'] = $moduleName;

        $this->initReplacement = false;
        return $this;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->replacement['MODULE_DESCRIPTION'] = $description;
        $this->initReplacement = false;
        return $this;
    }

    /**
     * @param $vendor
     * @return $this
     */
    public function setVendor($vendor)
    {
        $normalizer = new Normalizer();
        $vendor = $normalizer->vendor($vendor);

        $this->replacement['VENDOR'] = $vendor;
        $this->initReplacement = false;
        return $this;
    }

    /**
     * @param $authorName
     * @return $this
     */
    public function setAuthorName($authorName)
    {
        $this->replacement['AUTHOR_NAME'] = $authorName;
        $this->initReplacement = false;
        return $this;
    }

    /**
     * @param $authorEmail
     * @return $this
     */
    public function setAuthorEmail($authorEmail)
    {
        $this->replacement['AUTHOR_EMAIL'] = $authorEmail;
        $this->initReplacement = false;
        return $this;
    }

    /**
     * Autogen by Module Name and Vendor
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getModuleId()
    {
        $this->vaildRequirment();
        return $this->replacement['VENDOR'] . $this->normalizerModuleName;
    }

    /**
     * @return mixed
     */
    public function getDestinationPath($basedir)
    {
        $this->vaildRequirment();
        $path  = rtrim($basedir, '/');
        $path .= "/modules/{$this->replacement['VENDOR']}/$this->normalizerModuleName";
        return $path;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        $this->vaildRequirment();
        return $this->replacement['VENDOR'] . '\\' . $this->normalizerModuleName;
    }

    /**
     * @return array
     */
    public function getPlaceholders()
    {
        $array_keys = array_keys($this->initReplacement());
        array_walk($array_keys, function (&$item) {$item = "<$item>";});
        return $array_keys;
    }

    /**
     * @return array
     */
    public function getPlaceholderValues()
    {
        return array_values($this->initReplacement());
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function initReplacement()
    {
        if ($this->initReplacement) {
            return $this->replacement;
        }
        $this->replacement['MODULE_ID'] = $this->getModuleId();
        $this->replacement['MODULE_NAMESPACE'] = $this->getNamespace();
        $this->replacement['MODULE_NAMESPACE_QUOTED'] = $this->replacement['VENDOR'] . '\\\\' . $this->normalizerModuleName;
        $this->replacement['COMPOSER_NAME'] = $this->getComposerName();

        $this->initReplacement = true;
        return $this->replacement;
    }

    /**
     * @return string
     */
    protected function getComposerName()
    {
        $normalizer = new Normalizer();
        $composerName = $this->replacement['VENDOR'] . '-' . $this->replacement['MODULE_NAME'];
        $composerName = $normalizer->composerName($composerName);

        return $composerName;
    }

    protected function vaildRequirment()
    {
        if (empty($this->normalizerModuleName)) {
            throw new \InvalidArgumentException('Module Name is require');
        }
        if (empty($this->replacement['VENDOR'])) {
            throw new \InvalidArgumentException('Vendor is require');
        }
    }
}