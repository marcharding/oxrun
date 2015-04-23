<?php

namespace Oxrun\Command\Cache;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ClearCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ClearCommand());

        $command = $app->find('cache:clear');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains('Cache cleared.', $commandTester->getDisplay());
    }
}