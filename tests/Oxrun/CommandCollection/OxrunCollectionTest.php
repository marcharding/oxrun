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

/**
 * Class OxrunCollectionTest
 * @package Oxrun\CommandCollection\Tests
 * @group active
 */
class OxrunCollectionTest extends TestCase
{

    public function testHasInterfaceCommandCollection()
    {
        //Act
        $actual = new OxrunCollection();

        //Assert
        $this->assertInstanceOf(CommandCollection::class, $actual);
    }

    //todo more Tests
}
