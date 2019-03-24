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
use Oxrun\CommandCollection\EnableAdapter;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ReloadCommandTest
 * @package Oxrun\Command\Module
 */
class ReloadCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new ReloadCommand()));
        $app->add(new EnableAdapter(new DeactivateCommand()));
        $app->add(new EnableAdapter(new ClearCommand()));
        $app->add(new EnableAdapter(new ActivateCommand()));

        $command = $app->find('module:reload');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module' => 'oepaypal',
                '--shopId' => 1,
                '--force'  => true
            )
        );

        $this->assertContains('activated', $commandTester->getDisplay());
        $this->assertContains('Cache cleared', $commandTester->getDisplay());
        $this->assertContains('deactivated', $commandTester->getDisplay());
    }
}
