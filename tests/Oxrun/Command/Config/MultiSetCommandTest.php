<?php

namespace Oxrun\Command\Config;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MultiSetCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new MultiSetCommand());

        $command = $app->find('config:multiset');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'configfile' => "config:\n  1:\n    foobar: barfoo\n"
            )
        );

        $this->assertContains("Config foobar for shop 1 set", $commandTester->getDisplay());
    }

}