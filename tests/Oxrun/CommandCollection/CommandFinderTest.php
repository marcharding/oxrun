<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-07
 * Time: 07:53
 */

namespace Oxrun\Tests\CommandCollection;

use Oxrun\CommandCollection\Aggregator;
use Oxrun\CommandCollection\CommandFinder;
use PHPUnit\Framework\TestCase;

/**
 * @group active
 */
class CommandFinderTest extends TestCase
{

    public function testAddCommandsNeedOxidSourcePlace()
    {
        //Arrange
        $aggregator = $this->prophesize(Aggregator::class);
        $commandFinder = new CommandFinder();

        //Act
        $commandFinder->addRegister($aggregator->reveal(), true);
        $actual = $commandFinder->getPassNeedShopDir();

        //Assert
        $expect = [$aggregator->reveal()];
        $this->assertEquals($expect, $actual);
    }

    public function testAddCommands()
    {
        //Arrange
        $aggregator = $this->prophesize(Aggregator::class);
        $commandFinder = new CommandFinder();

        //Act
        $commandFinder->addRegister($aggregator->reveal());
        $actual = $commandFinder->getPass();

        //Assert
        $expect = [$aggregator->reveal()];
        $this->assertEquals($expect, $actual);
    }

    public function testRegisterFuncIsFluentDesign()
    {
        //Arrange
        $aggregator = $this->prophesize(Aggregator::class);
        $commandFinder = new CommandFinder();

        //Act
        $actual = $commandFinder->addRegister($aggregator->reveal());

        //Assert
        $this->assertEquals($commandFinder, $actual);
    }
}
