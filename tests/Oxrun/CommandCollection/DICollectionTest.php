<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-27
 * Time: 08:18
 */

namespace Oxrun\Tests\CommandCollection;

use Oxrun\CommandCollection;
use Oxrun\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;

/**
 * Class DICollectionTest
 * @package Oxrun\CommandCollection\Tests
 */
class DICollectionTest extends TestCase
{
    /**
     * @var \Oxrun\Application
     */
    private $app;

    public function testHasInterfaceCommandCollection()
    {
        //Act
        $actual = new CommandCollection\DICollection();

        //Assert
        $this->assertInstanceOf(CommandCollection::class, $actual);
    }

    public function testToLoadAllCommand()
    {
        //Arrange
        $oxrunCollection = new CommandCollection\DICollection();

        //Act
        $oxrunCollection->addFromDi(new Command('test'));
        $oxrunCollection->addCommandTo($this->app->reveal());

        //Assert
        $this->app->add(Argument::type(CommandCollection\EnableAdapter::class))->shouldHaveBeenCalled();
    }

    protected function setUp()
    {
        $this->app = $this->prophesize(\Oxrun\Application::class);
    }
}
