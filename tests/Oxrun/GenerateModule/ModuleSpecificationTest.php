<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 09.11.18
 * Time: 22:19
 */

namespace Oxrun\GenerateModule\Test;

use Oxrun\GenerateModule\ModuleSpecification;
use Oxrun\TestCase;

/**
 * Class ModuleSpecificationTest
 *
 * @package Oxrun\GenerateModule
 */
class ModuleSpecificationTest extends TestCase
{

    public function testSetAllExitsPlaceholder()
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();

        //Act
        $moduleSpecification
            ->setModuleName('Module Skeleton')
            ->setDescription('is a great OXDID Module')
            ->setVendor('oe')
            ->setAuthorName('Mrs. Developer')
            ->setAuthorEmail('dev@localhost');

        //Assert
        $expect = [
            '<MODULE_NAME>' => 'Module Skeleton',
            '<MODULE_DESCRIPTION>' => 'is a great OXDID Module',
            '<VENDOR>' => 'oe',
            '<AUTHOR_NAME>' => 'Mrs. Developer',
            '<AUTHOR_EMAIL>' => 'dev@localhost',
            '<MODULE_ID>' => 'oeModuleSkeleton',
            '<MODULE_NAMESPACE>' => 'oe\ModuleSkeleton',
            '<MODULE_NAMESPACE_QUOTED>' => 'oe\\\\ModuleSkeleton',
            '<COMPOSER_NAME>' => 'oe-module-skeleton',
        ];

        $this->assertEquals(array_keys($expect), $moduleSpecification->getPlaceholders());
        $this->assertEquals(array_values($expect), $moduleSpecification->getPlaceholderValues());
    }

    /**
     * @param $expectedParams
     * @dataProvider dataModuleNames
     */
    public function testGetModuleId($name, $vendor, $expect)
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();

        //Act
        $moduleSpecification
            ->setModuleName($name)
            ->setVendor($vendor);
        $actual = $moduleSpecification->getModuleId();

        //Assert
        $this->assertEquals($expect, $actual);
    }

    public function testGetModuleIdHasNotModuleName()
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();

        //Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module Name is require');

        //Act
        $moduleSpecification->getModuleId();
    }

    public function testGetModuleIdEmptyNameBecouseThatHasWrongChars()
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();

        //Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module Name is require');

        //Act
        $moduleSpecification->setModuleName(' !@#^&% ');
        $moduleSpecification->setVendor('aa');
        $moduleSpecification->getModuleId();
    }

    public function testGetModuleHasNotVendor()
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();

        //Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Vendor is require');

        //Act
        $moduleSpecification->setModuleName('BalBla');
        $moduleSpecification->getModuleId();
    }

    public function testGetNameSpace()
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();
        $moduleSpecification
            ->setModuleName('OxidModule')
            ->setVendor('tm');

        //Act
        $expect = 'tm\OxidModule';
        $actual = $moduleSpecification->getNamespace();

        //Asser
        $this->assertEquals($expect, $actual);
    }

    public function dataModuleNames()
    {
        return [
            [
                'name' => 'Module Skeleton',
                'vendor' => 'oe',
                'expect' => 'oeModuleSkeleton',
            ],
            [
                'name' => 'module skeleton',
                'vendor' => 'oe',
                'expect' => 'oeModuleSkeleton',
            ],
            [
                'name' => 'module skeleton@basic',
                'vendor' => 'oeShop',
                'expect' => 'oeshopModuleSkeletonBasic',
            ],
        ];
    }

    public function testGetDestinationPath()
    {
        //Arrange
        $moduleSpecification = new ModuleSpecification();
        $moduleSpecification->setModuleName('my Module Name');
        $moduleSpecification->setVendor('Vendor Name');

        //Act
        $expect = '/base/source/modules/vendorname/MyModuleName';
        $actual = $moduleSpecification->getDestinationPath('/base/source/');

        //Assert
        $this->assertEquals($expect, $actual);
    }


}
