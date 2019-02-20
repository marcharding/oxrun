<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-02-20
 * Time: 00:46
 */

namespace Oxrun\Command;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class OptionShopIdTest
 * @package Oxrun\Command
 */
class OptionShopIdTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->bootstrapOxid(false);

        $command = $app->find('help');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $display = $commandTester->getDisplay();
        $this->assertContains('-m, --shopId[=SHOPID]', $display);
    }

    public function testMarkShopWithId()
    {
        $app = new Application();
        $app->bootstrapOxid(false);
        $app->setAutoExit(false);

        $input = new ArrayInput(['help', '--shopId' => '4']);
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $app->run($input, $output);

        $this->assertArrayHasKey('shp', $_GET);
        $this->assertEquals(4, $_GET['shp']);
    }

    protected function tearDown()
    {
        unset($_GET['shp']);
        parent::tearDown();
    }
}
