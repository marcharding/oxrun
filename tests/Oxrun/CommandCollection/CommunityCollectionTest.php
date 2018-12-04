<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:41
 */

namespace Oxrun\CommandCollection\Tests;

use org\bovigo\vfs\vfsStream;
use Oxrun\CommandCollection;
use Oxrun\CommandCollection\CommunityCollection;
use Oxrun\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class CommunityTest
 * @package Oxrun\Application\CommandCollection\Tests
 */
class CommunityCollectionTest extends TestCase
{
    /**
     * @var \Oxrun\Application|ObjectProphecy
     */
    private $app;

    /**
     * @var string
     */
    private $oxid_fs_source;

    public function testHasInterfaceCommandCollection()
    {
        //Act
        $actual = new CommunityCollection();

        //Assert
        $this->assertInstanceOf(CommandCollection::class, $actual);
    }

    public function testThrowIsNotFoundInstalledJson()
    {
        //Arrange
        @unlink($this->oxid_fs_source . '/../vendor/composer/installed.json');
        $communityCollection = new CommunityCollection();

        //Assert
        $this->expectExceptionMessage('File not found: /composer/installed.json');

        //Act
        $communityCollection->addCommandTo($this->app->reveal());
    }

    public function testIsNotIntoSourceFolder()
    {
        //Arrange
        $this->app->bootstrapOxid(Argument::any())->willReturn(false);
        $communityCollection = new CommunityCollection();

        //Act
        $communityCollection->addCommandTo($this->app->reveal());

        //Assert
        $this->app->getShopDir()->shouldNotBeCalled();
    }

    public function testReadServiceYamlModule()
    {
        //Arrange
        $communityCollection = new CommunityCollection();

        //Assert
        $this->app->add(Argument::type( CommandCollection\EnableAdapter::class))->shouldBeCalled();

        //Act
        $communityCollection->addCommandTo($this->app->reveal());
    }

    public function testCanUseDifferentSpellingsClass()
    {
        //Arrange
        $communityCollection = new CommunityCollection();
        $this->mockShopDir('installed_one_package.json', 'different_spellings_class_name.yml');

        //Assert
        $this->app->add(Argument::type( CommandCollection\EnableAdapter::class))->shouldBeCalledTimes(2);

        //Act
        $communityCollection->addCommandTo($this->app->reveal());
    }

    public function testClassIsNotImplemendetDontStopToWork()
    {
        //Arrange
        $communityCollection = new CommunityCollection();
        $this->mockShopDir('installed_one_package.json', 'class_name_not_exists.yml');

        //Assert
        $this->expectExceptionMessage("- Class '\OxidEsales\DemoComponent\Command\NotImplemented' not found in Service: class.command.notimplemented'");
        $this->app->add(Argument::type( CommandCollection\EnableAdapter::class))->shouldBeCalled();

        //Act
        $communityCollection->addCommandTo($this->app->reveal());
    }

    protected function setUp()
    {
        $this->app = $this->prophesize(\Oxrun\Application::class);
        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->mockShopDir();
    }

    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/testData/HelloWorldCommand.php';
    }

    protected function mockShopDir(
        $installed_json = 'installed_one_package.json',
        $service_yml = 'standard.yml'
    ) {

        $oxid_fs = [];
        $oxid_fs['source']['bootstrap.php'] = '<?php OX_BASE_PATH';
        $oxid_fs['vendor']['composer']['installed.json'] = file_get_contents(__DIR__ . '/testData/'. $installed_json);
        $oxid_fs['vendor']['oxidesales']['democomponent']['services.yaml'] = file_get_contents(__DIR__ . '/testData/service_yml/'. $service_yml);

        $vfsStreamDirectory = vfsStream::setup('root', 0755, $oxid_fs);

        $this->oxid_fs_source = $vfsStreamDirectory->url() . '/source';

        $this->app->getShopDir()->willReturn($this->oxid_fs_source);
    }
}
