<?php

namespace Oxrun\Command\Module;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DeactivateCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new DeactivateCommand());

        $command = $app->find('module:deactivate');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'invoicepdf'
            )
        );

        $this->assertContains('Module invoicepdf deactivated.', $commandTester->getDisplay());
    }

}