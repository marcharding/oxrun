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
     * @var \Oxrun\Application|ObjectProphecy
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
        $communityCollection = new OxrunCollection();
        $assertOxrunCommand = function ($command) {
            $strpos = strpos(get_class($command), 'Oxrun\\Command');
            return $strpos === 0 ;
        };

        //Act
        $communityCollection->addCommandTo($this->app->reveal());

        //Assert
        $this->app->add(Argument::that($assertOxrunCommand))->shouldHaveBeenCalled();
    }

    protected function setUp()
    {
        $this->app = $this->prophesize(\Oxrun\Application::class);
    }
}
