<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-27
 * Time: 08:18
 */

namespace Oxrun\CommandCollection\Tests;

use Oxrun\CommandCollection;
use Oxrun\CommandCollection\OxrunCollection;
use Oxrun\TestCase;
use Prophecy\Argument;

/**
 * Class OxrunCollectionTest
 * @package Oxrun\CommandCollection\Tests
 */
class OxrunCollectionTest extends TestCase
{
    /**
     * @var \Oxrun\Application
     */
    private $app;

    public function testHasInterfaceCommandCollection()
    {
        //Act
        $actual = new OxrunCollection();

        //Assert
        $this->assertInstanceOf(CommandCollection::class, $actual);
    }

    public function testToLoadAllCommand()
    {
        //Arrange
        $oxrunCollection = new OxrunCollection();

        //Act
        $oxrunCollection->addCommandTo($this->app->reveal());

        //Assert
        $this->app->add(Argument::type(CommandCollection\EnableAdapter::class))->shouldHaveBeenCalled();
    }

    protected function setUp()
    {
        $this->app = $this->prophesize(\Oxrun\Application::class);
    }
}
