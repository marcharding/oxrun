<?php

namespace Oxrun\Command\Module;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ActivateCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ActivateCommand());
        $app->add(new DeactivateCommand());

        $command = $app->find('module:deactivate');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'invoicepdf'
            )
        );

        $command = $app->find('module:activate');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'invoicepdf'
            )
        );

        $this->assertContains('Module invoicepdf activated.', $commandTester->getDisplay());


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'invoicepdf'
            )
        );

        $this->assertContains('Module invoicepdf already activated.', $commandTester->getDisplay());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'not_and_existing_module'
            )
        );

        $this->assertContains('Cannot load module not_and_existing_module.', $commandTester->getDisplay());

    }

}