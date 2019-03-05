<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-05
 * Time: 08:01
 */

namespace Oxrun\CommandCollection\Aggregator;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class OxrunPassTest
 * @package Oxrun\CommandCollection\Aggregator
 */
class OxrunPassTest extends TestCase
{
    /**
     * @var Definition|ObjectProphecy|MethodProphecy
     */
    protected $definition;

    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    public function testFindOxrunCommands()
    {
        //Arrange
        $oxrunPass = new OxrunPass();

        //Assert
        $this->definition->addMethodCall(Argument::any(), Argument::any())->shouldBeCalled();

        //Act
        $oxrunPass->process($this->containerBuilder);
    }

    protected function setUp()
    {

        $this->definition = $this->prophesize(Definition::class);
        $this->definition->hasTag(Argument::any())->willReturn(false);

        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setDefinition('command_container', $this->definition->reveal());
    }
}
