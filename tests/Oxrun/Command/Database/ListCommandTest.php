<?php

namespace Oxrun\Command\Database;

use Oxrun\Application;
use Oxrun\TestCase;
use Oxrun\Command\Database\ListCommand as TestListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new TestListCommand());

        $command = $app->find('db:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains('Table', $commandTester->getDisplay());
        $this->assertContains('Type', $commandTester->getDisplay());
    }

}