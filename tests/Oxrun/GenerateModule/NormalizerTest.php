<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 08:39
 */

namespace Oxrun\GenerateModule;

use PHPUnit\Framework\TestCase;

/**
 * Class NormalizerTest
 * @package Oxrun\GenerateModule
 */
class NormalizerTest extends TestCase
{
    /**
     * @param $moduleName
     * @param $expect
     * @dataProvider dataModuleNames
     */
    public function testNormalizerModuleName($moduleName, $expect)
    {
        //Arrange
        $normalizer = new Normalizer();

        //Act
        $actual = $normalizer->moduleName($moduleName);

        //Assert
        $this->assertEquals($expect, $actual);

    }

    /**
     * @return array
     */
    public function dataModuleNames()
    {
        return [
            ['Module Skeleton', 'ModuleSkeleton'],
            ['Module Skeleton Base', 'ModuleSkeletonBase'],
            ['module skeleton base', 'ModuleSkeletonBase'],
            ['module skeleton@base', 'ModuleSkeletonBase'],
            ['ModuleSkeleton', 'ModuleSkeleton'],
            ['Moduleskeleton', 'Moduleskeleton'],
            ['Module-Skeleton', 'ModuleSkeleton'],
            ['Module-Skeleton-base', 'ModuleSkeletonBase'],
            ['  Module-Skeleton-base  ', 'ModuleSkeletonBase'],
            ['Modul$e-Sk%eleto)n ba&se2000', 'ModuleSkeletonBase2000'],
        ];
    }

    /**
     * @param $moduleName
     * @param $expect
     * @dataProvider dataVendor
     */
    public function testNormalizerVendor($vendor, $expect)
    {
        //Arrange
        $normalizer = new Normalizer();

        //Act
        $actual = $normalizer->vendor($vendor);

        //Assert
        $this->assertEquals($expect, $actual);

    }

    /**
     * @return array
     */
    public function dataVendor()
    {
        return [
            ['oe', 'oe'],
            ['oxid eshop', 'oxideshop'],
            ['oxid eShop', 'oxideshop'],
            ['oxid    eShop', 'oxideshop'],
            ['oxid-eShop', 'oxideshop'],
            ['ox!!i@d#e^S&h)op200', 'oxideshop200'],
        ];
    }

    /**
     * @dataProvider dataComposerName
     */
    public function testNormalizerComposerName($names, $expect)
    {
        //Arrange
        $normalizer = new Normalizer();

        //Act
        $actual = $normalizer->composerName($names);

        //Assert
        $this->assertEquals($expect, $actual);
    }

    public function dataComposerName()
    {
        return [
            ['oe module skeleton', 'oe-module-skeleton'],
            ['oe Module Skeleton', 'oe-module-skeleton'],
            ['oe Module Skeleton-Base', 'oe-module-skeleton-base'],
            ['oe Module Skeleton@Base', 'oe-module-skeleton-base'],
            ['oeModuleSkeleton', 'oe-module-skeleton'],
            ['oeMod$)uleSkeleton', 'oe-module-skeleton'],
            ['  oe   Mod$)uleSkeleton  ', 'oe-module-skeleton'],
        ];
    }

}
