<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:41
 */

namespace Oxrun\CommandCollection\Tests;

use Oxrun\CommandCollection;
use Oxrun\CommandCollection\CommunityCollection;
use Oxrun\TestCase;

/**
 * Class CommunityTest
 * @package Oxrun\Application\CommandCollection\Tests
 * @group active
 */
class CommunityCollectionTest extends TestCase
{

    public function testHasInterfaceCommandCollection()
    {
        //Act
        $actual = new CommunityCollection();

        //Assert
        $this->assertInstanceOf(CommandCollection::class, $actual);
    }

    //todo more Tests
}

