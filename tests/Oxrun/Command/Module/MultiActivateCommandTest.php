<?php

namespace Oxrun\Command\Module;

use Oxrun\Application;
use Oxrun\TestCase;
use Oxrun\Command\Cache\ClearCommand;
use Symfony\Component\Console\Tester\CommandTester;

class MultiActivateCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ActivateCommand());
        $app->add(new ClearCommand());
        $app->add(new DeactivateCommand());
        $app->add(new MultiActivateCommand());

        $command = $app->find('module:multiactivate');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => "whitelist:\n  1:\n    - oepaypal\n"
            )
        );

        $this->assertContains('Module oepaypal activated', $commandTester->getDisplay());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => "blacklist:\n  1:\n    - oepaypal\n"
            )
        );

        $this->assertContains("Module blacklisted: 'oepaypal'", $commandTester->getDisplay());
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => "whitelist:\n  1:\n    - not_and_existing_module\n"
            )
        );

        $this->assertContains('Cannot load module not_and_existing_module', $commandTester->getDisplay());

    }

}