<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-30
 * Time: 15:58
 */

namespace Oxrun\Tests\CommandCollection;

use Oxrun\Command\EnableInterface;
use Oxrun\CommandCollection\EnableAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
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
        $command = $this->prophesize(Command::class);
        $enableAdapter = new EnableAdapter($command->reveal());
        $symfonyCommandClass = new \ReflectionClass(Command::class);
        $command_methods = $symfonyCommandClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        /** @var \ReflectionMethod $reflectionMethod */
        foreach ($command_methods as $reflectionMethod) {
            $method_name = $reflectionMethod->getName();

            if ($method_name == '__construct') {
                continue;
            }

            $reflectionParameters = $reflectionMethod->getParameters();
            $method_arguments = $this->simulateArguments($reflectionParameters);

            $mock_method = $command->$method_name();
            $this->addMockArguments($mock_method, count($reflectionParameters));

            //Assert
            $mock_method->shouldBeCalled();

            //Act
            call_user_func_array([$enableAdapter, $method_name], $method_arguments);
        }
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

    /**
     * @param MethodProphecy $mock_method
     * @param integer        $numberOfParameters
     */
    private function addMockArguments($mock_method, $numberOfParameters)
    {
        $arguments = [];
        for ($i = 1; $i <= $numberOfParameters; $i++) {
            $arguments[] = Argument::any();
        }
        if (!empty($arguments)) {
            $mock_method->withArguments($arguments);
        }
    }

    /**
     * @param array $parameter
     * @return array
     */
    private function simulateArguments($parameter)
    {
        $arguments = [];

        /** @var \ReflectionParameter $reflectionParameter */
        foreach ($parameter as $reflectionParameter) {
            $parameterClass = $reflectionParameter->getClass();
            if ($parameterClass) {
                $classOrInterface = $parameterClass->getName();
                $arguments[] = $this->prophesize($classOrInterface)->reveal();
            } else {
                $arguments[] = '';
            }
        }

        return $arguments;
    }
}
