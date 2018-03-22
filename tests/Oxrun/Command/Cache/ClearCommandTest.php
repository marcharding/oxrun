<?php

namespace Oxrun\Command\Cache;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ClearCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new ClearCommand());

        $command = $app->find('cache:clear');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--force' => 1
            )
        );

        $this->assertContains('Cache cleared.', $commandTester->getDisplay());
    }

    /**
     * @expectedException \Exception
     */
    public function testDontClearCompileFolderIfIsNotSameOwner()
    {
        $app = new Application();
        $app->add(new ClearCommand());

        $oxconfigfile = new \oxConfigFile($app->getShopDir() . DIRECTORY_SEPARATOR . 'config.inc.php');
        $compileDir   = $oxconfigfile->getVar('sCompileDir');

        $owner = fileowner($compileDir);
        $current_owner = posix_getuid();

        if ($current_owner == $owner) {
            throw new \PHPUnit_Framework_SkippedTestError('Test can\'t be testet, becouse the compileDir has the same owner ');
        }

        $command = $app->find('cache:clear');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );
    }
}
