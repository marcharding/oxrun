<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-02
 * Time: 17:50
 */

namespace Oxrun\Command;

use Oxrun\Application;
use Oxrun\Command\Misc\GenerateYamlMultiSetCommand;
use Oxrun\CommandCollection\EnableAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateYamlMultiSetCommandTest
 * @package Oxrun\Command
 */
class GenerateYamlMultiSetCommandTest extends TestCase
{
    protected static $unlinkFile = null;

    public function testExecute()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new GenerateYamlMultiSetCommand()));

        $command = $app->find('misc:generate:yaml:multiset');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $expectPath = self::$unlinkFile = $app->getOxrunConfigPath() . 'shopConfigs.yml';

        $this->assertContains('Config saved. use `oxrun config:multiset shopConfigs.yml`', $commandTester->getDisplay());
        $this->assertFileExists($expectPath);
    }

    protected function tearDown()
    {
        if (self::$unlinkFile) {
            @unlink(self::$unlinkFile);
        }

        parent::tearDown();
    }
}
