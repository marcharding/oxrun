<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-02-20
 * Time: 01:33
 */

namespace Oxrun\Oxid;

use Oxrun\Application;
use Oxrun\Command\Oxid\ShopListCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ShopListCommandTest
 * @package Oxrun\Oxid
 */
class ShopListCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $shopListCommand = new ShopListCommand();
        $app->bootstrapOxid($shopListCommand->needDatabaseConnection());
        $app->add($shopListCommand);

        $command = $app->find('oxid:shops');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $display = $commandTester->getDisplay();
        $this->assertContains('ShopId', $display);
        $this->assertContains('Shop name', $display);
        $this->assertContains('OXID eShop', $display);
    }

}
