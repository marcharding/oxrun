<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-04
 * Time: 21:39
 */

namespace Oxrun\Tests\CommandCollection\Aggregator;

use Oxrun\CommandCollection\Aggregator\CacheCheck;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Class CacheCheckTest
 * @package Oxrun\CommandCollection\Aggregator
 */
class CacheCheckTest extends TestCase
{
    public function testCanSaveFileRessourceArray()
    {
        //Arrange
        CacheCheck::addFile(__FILE__);

        //Act
        $resource = CacheCheck::getResource();

        //Assert
        $this->assertContainsOnlyInstancesOf(FileResource::class, $resource);
    }

    protected function tearDown()
    {
        CacheCheck::clean();
        parent::tearDown();
    }
}
