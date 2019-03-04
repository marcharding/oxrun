<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:41
 */

namespace Oxrun\Tests\CommandCollection\Aggregator;

use Oxrun\CommandCollection;
use Oxrun\CommandCollection\Aggregator;
use Oxrun\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class CommunityPassTest
 * @package Oxrun\CommandCollection\Tests
 */
class CommunityPassTest extends TestCase
{
    /**
     * @var string
     */
    private $oxid_fs_source;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var Definition|ObjectProphecy
     */
    private $definition;

    public function testThrowIsNotFoundInstalledJson()
    {
        //Arrange
        @unlink($this->oxid_fs_source . '/../vendor/composer/installed.json');
        $communityCollection = new Aggregator\CommunityPass($this->oxid_fs_source);

        //Assert
        $this->expectExceptionMessage('File not found: /composer/installed.json');

        //Act
        $communityCollection->process($this->containerBuilder);
    }

    public function testReadServiceYamlModule()
    {
        //Arrange
        $communityPass = new Aggregator\CommunityPass($this->oxid_fs_source);

        //Assert
        $this->definition->addMethodCall(Argument::is('addFromDi'), Argument::any())->shouldBeCalled();

        //Act
        $communityPass->process($this->containerBuilder);
    }

    public function testCanUseDifferentSpellingsClass()
    {
        //Arrange
        $this->mockShopDir('installed_one_package.json', 'different_spellings_class_name.yml');
        $communityPass = new Aggregator\CommunityPass($this->oxid_fs_source);

        //Assert
        $this->definition->addMethodCall(Argument::is('addFromDi'), Argument::any())->shouldBeCalledTimes(2);

        //Act
        $communityPass->process($this->containerBuilder);
    }

    public function testCheckCacheFiles()
    {
        //Arrange
        $communityPass = new Aggregator\CommunityPass($this->oxid_fs_source);
        $this->definition->addMethodCall(Argument::is('addFromDi'), Argument::any())->shouldBeCalled();

        //Act
        $communityPass->process($this->containerBuilder);

        $actual = Aggregator\CacheCheck::getResource();

        //Assert
        $this->assertCount(2, $actual);
    }

    protected function setUp()
    {
        $this->mockShopDir();

        $this->definition = $this->prophesize(Definition::class);
        $this->definition->hasTag(Argument::any())->willReturn(false);

        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setDefinition('command_container', $this->definition->reveal());
    }

    public static function setUpBeforeClass()
    {
        require_once self::getTestData('HelloWorldCommand.php');
    }

    protected function tearDown()
    {
        Aggregator\CacheCheck::clean();
        parent::tearDown();
    }

    protected function mockShopDir(
        $installed_json = 'installed_one_package.json',
        $service_yml = 'standard.yml'
    ) {
        $oxid_fs['source']['bootstrap.php'] = '<?php OX_BASE_PATH';
        $oxid_fs['vendor']['composer']['installed.json'] = file_get_contents(self::getTestData($installed_json));
        $oxid_fs['vendor']['oxidesales']['democomponent']['services.yaml'] = file_get_contents(self::getTestData('service_yml/'. $service_yml));

        $this->oxid_fs_source = $this->fillShopDir($oxid_fs)->getVfsStreamUrl();

    }

    protected static function getTestData($filepath)
    {
        return __DIR__ . '/../testData/' . $filepath;
    }
}
