<?php

namespace Oxrun\Command\Database;

use Oxrun\Application;
use Oxrun\CommandCollection\EnableAdapter;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AnonymizeCommandTest
 * @package Oxrun\Command\Database
 * @group active
 */
class AnonymizeCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new AnonymizeCommand()));

        $command = $app->find('db:anonymize');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--debug' => true,
                '--keepdomain' => '@shoptimax.de',
            ),
            ['interactive' => false]
        );

        $this->assertContains('oxaddress', $commandTester->getDisplay());
        $this->assertContains('Anonymizing done.', $commandTester->getDisplay());
    }
}
