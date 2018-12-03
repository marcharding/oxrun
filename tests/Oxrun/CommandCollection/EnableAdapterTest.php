<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-30
 * Time: 15:58
 */

namespace Oxrun\CommandCollection\Tests;

use Oxrun\Command\EnableInterface;
use Oxrun\CommandCollection\EnableAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class EnableAdapterTest
 * @package Oxrun\CommandCollection\Tests
 */
class EnableAdapterTest extends TestCase
{
    public function testCommandIsEnableFromCommunity()
    {
        //Arrange
        $command = $this->prophesize(Command::class);
        $command->getApplication()->willReturn(null);

        //Act
        $enableAdapter = new EnableAdapter($command->reveal());
        $command->isEnabled()->willReturn(true)->shouldBeCalled();

        //Assert
        $this->assertTrue($enableAdapter->isEnabled());
    }

    public function testAdapterPassMethodsToCommand()
    {
        //Arrange
        /** @var Command $command */
        $command = $this->prophesize(Command::class);

        //Assert
        $command->getHelp()->shouldBeCalled();
        $command->setAliases(Argument::is('BlBla'))->shouldBeCalled();
        $command->run(Argument::type(ArrayInput::class), Argument::type(NullOutput::class))->shouldBeCalled();

        //Act
        $enableAdapter = new EnableAdapter($command->reveal());
        $enableAdapter->getHelp();
        $enableAdapter->setAliases('BlBla');
        $enableAdapter->run(new ArrayInput([]), new NullOutput());
    }

    public function testByOxrunApplicationEnableIfFoundOxidFramework()
    {
        //Arrange
        /** @var Command|ObjectProphecy|EnableInterface $command */
        $command = $this->prophesize(Command::class);
        $command->willImplement(EnableInterface::class);
        /** @var \Oxrun\Application $app */
        $app = $this->prophesize(\Oxrun\Application::class);

        //Assert
        $command->getApplication()->willReturn($app->reveal())->shouldBeCalled();
        $command->needDatabaseConnection()->willReturn(true)->shouldBeCalled();
        $command->isEnabled()->shouldNotBeCalled();
        $app->bootstrapOxid(Argument::is(true))->willReturn(false)->shouldBeCalled();

        //Act
        $enableAdapter = new EnableAdapter($command->reveal());
        $this->assertFalse($enableAdapter->isEnabled());
    }

    public function testShouldBeAOxrunCommand()
    {
        //Arrange
        /** @var \Oxrun\Application $app */
        $app = $this->prophesize(\Oxrun\Application::class);
        /** @var Command|ObjectProphecy|EnableInterface $command */
        $command = $this->prophesize(Command::class);
        $command->getApplication()->willReturn($app->reveal());
        $commandReveal = $command->reveal();

        //Assert
        $this->expectExceptionMessage('Command `' . get_class($commandReveal) . '` must implement: ' . EnableInterface::class);

        //Act
        $enableAdapter = new EnableAdapter($commandReveal);
        $enableAdapter->isEnabled();
    }

}
