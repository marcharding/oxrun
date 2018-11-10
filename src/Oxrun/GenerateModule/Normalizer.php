<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 08:38
 */

namespace Oxrun\GenerateModule;

/**
 * Class Normalizer
 *
 * @package Oxrun\GenerateModule
 */
class Normalizer
{
    /**
     * @see \Oxrun\GenerateModule\NormalizerTest::dataModuleNames
     *
     * @param $moduleName
     * @return string
     */
    public function moduleName($moduleName)
    {
        $moduleName = preg_replace_callback('/[\s@-]([a-z])/', function($m){return strtoupper($m[1]);}, $moduleName);
        $moduleName = ucfirst($moduleName);
        $moduleName = preg_replace('/[^A-Za-z0-9]/', '', $moduleName); //Remove not validate chars

        return $moduleName;
    }

    /**
     * @see \Oxrun\GenerateModule\NormalizerTest::dataVendor
     *
     * @param $vendor
     * @return string
     */
    public function vendor($vendor)
    {
        $vendor = strtolower($vendor);
        $vendor = preg_replace('/[^A-Za-z0-9]/', '', $vendor); //Remove not validate chars

        return $vendor;
    }

    /**
     * @see \Oxrun\GenerateModule\NormalizerTest::dataComposerName
     *
     * @param $composerName
     * @return string
     */
    public function composerName($composerName)
    {
        $composerName = preg_replace('/[A-Z]+/', '-$0', $composerName);
        $composerName = preg_replace('/[\s@-]+/', '-', $composerName);
        $composerName = preg_replace('/[^A-Za-z0-9-]/', '', $composerName); //Remove not validate chars
        $composerName = strtolower($composerName);
        $composerName = trim($composerName, '-');

        return $composerName;
    }
}
