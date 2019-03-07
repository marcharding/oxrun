<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-05
 * Time: 07:07
 */

namespace Oxrun\Tests\CommandCollection;

use Oxrun\Application;
use Oxrun\CommandCollection\CacheCheck;
use Oxrun\CommandCollection\ContainerCollection;
use Oxrun\CommandCollection\EnableAdapter;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class ContainerCollectionTest
 * @package Oxrun\CommandCollection
 */
class ContainerCollectionTest extends \Oxrun\TestCase
{
    /**
     * @var Application|ObjectProphecy|MethodProphecy
     */
    private $app;

    /**
     * @var string
     */
    private $oxid_fs_source;

    public function testAddOxrunCommandWithoutFindBootstrap()
    {
        //Arrgange
        $this->app->bootstrapOxid(Argument::any())->willReturn(false);
        $this->app->getShopDir()->willReturn('');

        //Assert
        $this->app->add(Argument::type(EnableAdapter::class))->shouldBeCalled();

        //Act
        $containerCollection = new ContainerCollection();
        $containerCollection->addCommandTo($this->app->reveal());
    }

    public function testCreateCommandContainer()
    {
        //Arrgange
        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->app->getShopDir()->willReturn($this->oxid_fs_source);

        //Act
        $containerCollection = new ContainerCollection();
        $containerCollection->addCommandTo($this->app->reveal());

        //Arrange
        $this->assertFileExists($this->oxid_fs_source.'/../vendor/oxidprojects/OxrunCommands.php');
        $this->assertFileExists($this->oxid_fs_source.'/../vendor/oxidprojects/OxrunCommands.php.meta');
        $this->assertContains('installed.json', file_get_contents($this->oxid_fs_source.'/../vendor/oxidprojects/OxrunCommands.php.meta'));
    }

    public function testAlwayFindOxRunCommandIfThrowException()
    {
        //Arrgange
        @unlink($this->oxid_fs_source.'/../vendor/composer/installed.json');

        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->app->getShopDir()->willReturn($this->oxid_fs_source);

        //Assert
        $this->app->add(Argument::type(EnableAdapter::class))->shouldBeCalled();

        //Act
        $containerCollection = new ContainerCollection();
        $containerCollection->addCommandTo($this->app->reveal());
    }

    protected function setUp()
    {
        $this->app = $this->prophesize(Application::class);
        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->app->add(Argument::type(EnableAdapter::class))->willReturn('');

        $this->mockShopDir();
    }


    protected function tearDown()
    {
        CacheCheck::clean();
        parent::tearDown();
    }

    protected function mockShopDir() {

        $oxid_fs['vendor']['composer']['installed.json'] = file_get_contents(self::getTestData('installed_one_package.json'));

        $this->oxid_fs_source = $this->fillShopDir($oxid_fs)->getVfsStreamUrl();
    }

    protected static function getTestData($filepath)
    {
        return __DIR__ . '/testData/' . $filepath;
    }
}
