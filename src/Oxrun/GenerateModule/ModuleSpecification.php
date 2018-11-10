<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 09.11.18
 * Time: 21:06
 */

namespace Oxrun\GenerateModule;


class ModuleSpecification
{
    /**
     * Has all placeholder
     *
     * @var array
     */
    protected $replacement = [];

    /**
     * @param $moduleName
     * @return $this
     */
    private $normalizerModuleName;

    /**
     * @param $moduleName
     * @return $this
     */
    public function setModuleName($moduleName)
    {
        $normalizer = new Normalizer();

        $this->normalizerModuleName = $normalizer->moduleName($moduleName);
        $this->replacement['MODULE_NAME'] = $moduleName;

        return $this;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->replacement['MODULE_DESCRIPTION'] = $description;
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
        return $this;
    }

    /**
     * @param $authorName
     * @return $this
     */
    public function setAuthorName($authorName)
    {
        $this->replacement['AUTHOR_NAME'] = $authorName;
        return $this;
    }

    /**
     * @param $authorEmail
     * @return $this
     */
    public function setAuthorEmail($authorEmail)
    {
        $this->replacement['AUTHOR_EMAIL'] = $authorEmail;
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
        if (empty($this->normalizerModuleName)) {
            throw new \InvalidArgumentException('Module Name is require');
        }
        if (empty($this->replacement['VENDOR'])) {
            throw new \InvalidArgumentException('Vendor is require');
        }

        return $this->replacement['VENDOR'] . $this->normalizerModuleName;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getReplacement()
    {
        $this->replacement['MODULE_ID'] = $this->getModuleId();
        $this->replacement['MODULE_NAMESPACE'] = $this->replacement['VENDOR'] . '\\' . $this->normalizerModuleName;
        $this->replacement['MODULE_NAMESPACE_QUOTED'] = $this->replacement['VENDOR'] . '\\\\' . $this->normalizerModuleName;
        $this->replacement['COMPOSER_NAME'] = $this->getComposerName();

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


}