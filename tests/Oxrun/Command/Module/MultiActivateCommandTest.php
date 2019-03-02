<?php

namespace Oxrun\Command\Module;

use Oxrun\Application;
use Oxrun\CommandCollection\EnableAdapter;
use Oxrun\TestCase;
use Oxrun\Command\Cache\ClearCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class MultiActivateCommandTest
 * @package Oxrun\Command\Module
 */
class MultiActivateCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new ActivateCommand()));
        $app->add(new EnableAdapter(new ClearCommand()));
        $app->add(new EnableAdapter(new DeactivateCommand()));
        $app->add(new EnableAdapter(new MultiActivateCommand()));

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
