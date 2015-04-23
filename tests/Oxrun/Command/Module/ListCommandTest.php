<?php

namespace Oxrun\Command\Module;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ListCommand());

        $command = $app->find('module:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );

        $this->assertContains('invoice', $commandTester->getDisplay());
    }

}