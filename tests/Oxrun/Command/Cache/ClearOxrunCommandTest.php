<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <developer@tobimat.eu>
 */

namespace Oxrun\Command\Cache;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ClearOxrunCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ClearOxrunCommand());

        $command = $app->find('cache:clear:oxrun');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains('Oxrun cache cleared.', $commandTester->getDisplay());
    }
}
