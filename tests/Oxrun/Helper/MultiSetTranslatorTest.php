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

    public function testConfigNotFound()
    {
        //Arrange
        $multiSetTranslator = new MultiSetTranslator();

        //Assert
        $this->expectException(FileNotFoundException::class);

        //Act
        $multiSetTranslator->configFile('irgendwas_datei.yml', 0);
    }

    public function testYamlHasNotAConfigSection()
    {
        //Arrange
        $multiSetTranslator = new MultiSetTranslator();
        $vfs = vfsStream::setup('oxrun', 0755, [
            "config.yml" => 'andere: "config"',
        ])->url();

        //Assert
        $this->expectException(\Exception::class);

        //Act
        $multiSetTranslator->configFile($vfs.'/config.yml', 0);
    }

    public function testTranslateConfig()
    {
        //Arrange
        $multiSetTranslator = new MultiSetTranslator();

        $vfs = vfsStream::setup('oxrun', 0755, [
            "actual.yml" => file_get_contents(__DIR__.'/testData/plan_config.yml'),
            "expect.yml" => file_get_contents(__DIR__.'/testData/translated_config.yml'),
        ])->url();

        //Act
        $multiSetTranslator
            ->configFile($vfs . '/actual.yml', 0)
            ->save();

        //Assert
        $this->assertFileEquals($vfs . '/expect.yml', $vfs . '/actual.yml');
    }

}
