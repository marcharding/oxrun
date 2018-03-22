<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 * Date: 07.06.17
 * Time: 07:56
 */

namespace Oxrun\Command\Module;

use Oxrun\Application;
use Oxrun\Command\Cache\ClearCommand;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ReloadCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ReloadCommand());
        $app->add(new DeactivateCommand());
        $app->add(new ClearCommand());
        $app->add(new ActivateCommand());

        $command = $app->find('module:reload');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'invoicepdf',
                '--force'  => true
            )
        );

        $this->assertContains('activated', $commandTester->getDisplay());
        $this->assertContains('Cache cleared', $commandTester->getDisplay());
        $this->assertContains('deactivated', $commandTester->getDisplay());
    }
}
