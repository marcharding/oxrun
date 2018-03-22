<?php

namespace Oxrun\Command\Cache;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ClearCommandTest extends TestCase
{
    /**
     * @var string
     */
    protected $change_back_to_origin = null;

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
            $this->change_back_to_origin = ['userid' => $owner, 'path' => $compileDir];
            chown($compileDir, 'daemon');
            $hasChange = fileowner($compileDir);
            if ($hasChange == $owner) {
                throw new \PHPUnit_Framework_SkippedTestError('Process has\'t premission to change owner ');
            };
        }

        $command = $app->find('cache:clear');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        if ($this->change_back_to_origin !== null) {
            chown($this->change_back_to_origin['path'], $this->change_back_to_origin['userid']);
            $this->change_back_to_origin = null;
        }
        parent::tearDown();
    }


}
