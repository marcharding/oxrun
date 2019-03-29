<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 2019-03-29
 * Time: 07:12
 */

namespace Oxrun\Tests\Helper;

use org\bovigo\vfs\vfsStream;
use Oxrun\Application;
use Oxrun\Helper\MultiSetTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class MultiSetTranslatorTest
 * @package Oxrun\Helper
 */
class MultiSetTranslatorTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        if (!defined('OX_BASE_PATH')) (new Application())->bootstrapOxid(false);
    }

    public function testYamlHasNotAConfigSection()
    {
        //Arrange
        $multiSetTranslator = new MultiSetTranslator();

        $ymltxt = 'andere: "config"';

        //Assert
        $this->expectException(\Exception::class);

        //Act
        $multiSetTranslator->configFile($ymltxt, 0);
    }

    public function testTranslateConfig()
    {
        //Arrange
        $multiSetTranslator = new MultiSetTranslator(2);

        $ymltxt = file_get_contents(__DIR__.'/testData/plan_config.yml');
        $expect = file_get_contents(__DIR__.'/testData/translated_config.yml');

        //Act
        $actual = $multiSetTranslator->configFile($ymltxt, 0);

        //Assert
        $this->assertEquals($expect, $actual);
    }

}
