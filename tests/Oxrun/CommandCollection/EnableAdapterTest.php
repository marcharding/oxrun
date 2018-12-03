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
    /**
     * @var \Oxrun\Application|ObjectProphecy
     */
    private $app;

    /**
     * @var Command|ObjectProphecy|EnableInterface
     */
    private $command;

    protected function setUp()
    {
        $this->app = $this->prophesize(\Oxrun\Application::class);
        $this->command = $this->prophesize(Command::class);
        $this->command->getApplication()->willReturn($this->app->reveal());
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

    public function testCommunityCommandIsDisable()
    {
        //Arrange
        $enableAdapter = new EnableAdapter($this->command->reveal());

        //Assert
        $this->command->isEnabled()->willReturn(false)->shouldBeCalled();

        //Act
        $actual = $enableAdapter->isEnabled();

        //Assert
        $this->assertFalse($actual);
    }

    public function testCommunityCommandIsEnable()
    {
        //Arrange
        $enableAdapter = new EnableAdapter($this->command->reveal());
        $this->command->isEnabled()->willReturn(true);

        //Assert
        $this->app->bootstrapOxid(Argument::is(false))->willReturn(true)->shouldBeCalled();

        //Act
        $actual = $enableAdapter->isEnabled();

        //Assert
        $this->assertTrue($actual);
    }

    /**
     * @group active
     */
    public function testOxrunCommandCheckWithDatabaseConnection()
    {
        //Arrange
        $this->command = $this->prophesize(Command::class);
        $this->command->willImplement(EnableInterface::class);
        $this->command->getApplication()->willReturn($this->app->reveal());
        $enableAdapter = new EnableAdapter($this->command->reveal());

        //Assert
        $this->command->needDatabaseConnection()->willReturn(true)->shouldBeCalled();
        $this->app->bootstrapOxid(Argument::is(true))->willReturn(false)->shouldBeCalled();

        //Act
        $actual = $enableAdapter->isEnabled();

        //Assert
        $this->assertFalse($actual);
    }
}
