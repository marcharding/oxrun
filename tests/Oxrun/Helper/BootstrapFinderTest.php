<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 2019-05-03
 * Time: 14:06
 */

namespace Oxrun\Tests\Helper;

use Oxrun\Helper\BootstrapFinder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;

class BootstrapFinderTest extends TestCase
{
    protected static $preSave = ['argv' => [], 'currentWorkingDirectory'  => __DIR__];

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        self::$preSave['argv'] = $_SERVER['argv'];
        self::$preSave['currentWorkingDirectory'] = getcwd();
    }

    /**
     * @dataProvider pathArguments
     */
    public function testFindeBootstrapByArgument($url)
    {
        //Arrange
        $_SERVER['argv'] = ['', '--shopDir', $url];
        $bootstrapFinder = new BootstrapFinder(null);

        //Act
        $actual = $bootstrapFinder->isFound();
        $actual_path = $bootstrapFinder->getShopDir();

        //Assert
        $this->assertTrue($actual);
        $this->assertEquals(__DIR__ . '/testData/eShopDir/source', $actual_path);
    }

    public function pathArguments()
    {
        return [
            'sourceFolder' => [__DIR__ . '/testData/eShopDir/source'],
            'installFolder' => [__DIR__ . '/testData/eShopDir/'],
        ];
    }

    public function testArgumentPathIsWrong()
    {
        //Arrange
        $_SERVER['argv'] = ['', '--shopDir', '/irgendow'];
        $bootstrapFinder = new BootstrapFinder(null);

        //Act
        $actual = $bootstrapFinder->isFound();

        //Assert
        $this->assertFalse($actual);
    }

    /**
     * @dataProvider pathIntoTree
     */
    public function testFindeBootstrapIntoTree($path)
    {
        //Arrange
        chdir($path);
        $bootstrapFinder = new BootstrapFinder(null);

        //Act
        $actual = $bootstrapFinder->isFound();
        $actual_path = $bootstrapFinder->getShopDir();

        //Assert
        $this->assertTrue($actual);
        $this->assertEquals(__DIR__ . '/testData/eShopDir/source', $actual_path);
    }

    public function pathIntoTree()
    {
        return [
            'installFolder' => [__DIR__ . '/testData/eShopDir/'],
            'sourceFolder' => [__DIR__ . '/testData/eShopDir/source'],
            'moduleFolder' => [__DIR__ . '/testData/eShopDir/source/modules/opensource'],
        ];
    }

    /**
     * @dataProvider pathIntoTree
     */
    public function testFindeBootstrapByEnv($path)
    {
        //Arrange
        putenv('OXID_SHOP_DIR='.$path);

        $bootstrapFinder = new BootstrapFinder(null);

        //Act
        $actual = $bootstrapFinder->isFound();
        $actual_path = $bootstrapFinder->getShopDir();

        //Assert
        $this->assertTrue($actual);
        $this->assertEquals(__DIR__ . '/testData/eShopDir/source', $actual_path);
    }

    public function testDoNotLoadWrongBootstrap()
    {
        //Arrange
        chdir(__DIR__ . "/testData/eShopDir/wrongSource");
        $bootstrapFinder = new BootstrapFinder(null);

        //Act
        $actual = $bootstrapFinder->isFound();

        //Assert
        $this->assertFalse($actual);
        $this->assertFalse(defined('WRONG_BOOTSTRAP_IS_LOADED'));
    }

    public function testReloadAutoloader()
    {
        //Arrange
        chdir(__DIR__ . '/testData/eShopDir/source');
        $autoloader = $this->prophesize(\Composer\Autoload\ClassLoader::class);
        $bootstrapFinder = new BootstrapFinder($autoloader->reveal());

        //Act
        $bootstrapFinder->isFound();

        //Assert
        $autoloader->unregister()->shouldHaveBeenCalled();
        $autoloader->register(Argument::is(true))->shouldHaveBeenCalled();
    }

    public function testOxrunIsPackageIntoOxidCompose()
    {
        //Arrange
        $bootstrapFinderPHP = file_get_contents( __DIR__ . '/../../../src/Oxrun/Helper/BootstrapFinder.php');
        $bootstrapFinderPHP = str_replace('namespace Oxrun\\Helper;', 'namespace Oxrun\\Test\\Mock\\Helper;',  $bootstrapFinderPHP);
        file_put_contents(__DIR__.'/testData/eShopDir/vendor/oxidprojects/oxrun/src/Oxrun/Helper/BootstrapFinder.php', $bootstrapFinderPHP);
        include_once __DIR__.'/testData/eShopDir/vendor/oxidprojects/oxrun/src/Oxrun/Helper/BootstrapFinder.php';

        $bootstrapFinder = new \Oxrun\Test\Mock\Helper\BootstrapFinder(null);

        //Act
        $actual = $bootstrapFinder->isFound();
        $actual_path = $bootstrapFinder->getShopDir();

        //Assert
        $this->assertTrue($actual);
        $this->assertEquals(__DIR__ . '/testData/eShopDir/source', $actual_path);
    }


    protected function tearDown()
    {
        parent::tearDown();
        putenv('OXID_SHOP_DIR=');
        $_SERVER['argv'] = [];
        @unlink(__DIR__.'/testData/eShopDir/vendor/oxidprojects/oxrun/src/Oxrun/Helper/BootstrapFinder.php');
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass()
    {
        $_SERVER['argv'] = self::$preSave['argv'];
        chdir(self::$preSave['currentWorkingDirectory']);
        self::$preSave = [];
    }

}
