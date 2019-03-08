<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 07.03.19
 * Time: 11:45
 */

namespace Oxrun\Tests\CommandCollection\Aggregator;

use Oxrun\CommandCollection\Aggregator\CustomPass;
use Oxrun\CommandCollection\CacheCheck;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class CustomPassTest
 * @package Oxrun\CommandCollection\Aggregator
 */
class CustomPassTest extends \Oxrun\TestCase
{
    /**
     * @var Definition|ObjectProphecy|MethodProphecy
     */
    protected $definition;

    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    public function testLoadCustomCommand()
    {
        //Arrange
        $customCommand = new CustomPass();
        $customCommand->setOxrunConfigDir($this->mockCommand('CustomCommand.php'));

        //Assert
        $this->definition->addMethodCall(Argument::any(), Argument::any())->shouldBeCalled();

        //Act
        $customCommand->process($this->containerBuilder);

        //Assert
        $this->assertCount(1, CacheCheck::getResource());
    }

    public function testHasNotTheSameNamespace()
    {
        //Arrange
        $customCommand = new CustomPass();
        $customCommand->setOxrunConfigDir($this->mockCommand('WrongNamespaceCommand.php'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Error loading class 'Oxrun\CustomCommand\WrongNamespaceCommand'. Trace: Oxrun\CommandCollection\Aggregator\CustomPass");

        //Act
        $customCommand->process($this->containerBuilder);
    }

    public function testClassIsNotCompatibleClass()
    {
        //Arrange
        $customCommand = new CustomPass();
        $customCommand->setOxrunConfigDir($this->mockCommand('IsNotACommand.php'));
        $output = new BufferedOutput();
        $customCommand->setConsoleOutput($output);

        //Act
        $customCommand->process($this->containerBuilder);

        //Assert
        $this->assertEquals('Oxrun\CustomCommand\IsNotACommand is not a compatible command'.PHP_EOL, $output->fetch());
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->definition = $this->prophesize(Definition::class);
        $this->definition->hasTag(Argument::any())->willReturn(false);

        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setDefinition('command_container', $this->definition->reveal());
    }

    protected function tearDown()
    {
        CacheCheck::clean();
        parent::tearDown();
    }

    protected function mockCommand($filename) {
        $oxid_fs['oxrun_config']['commands'][$filename] = file_get_contents(self::getTestData($filename));

        return $this->fillShopDir($oxid_fs)->getVfsStreamUrl() . '/../oxrun_config/';
    }

    protected static function getTestData($filepath)
    {
        return __DIR__ . '/../testData/commands/' . $filepath;
    }
}
