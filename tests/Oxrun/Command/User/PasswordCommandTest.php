<?php

namespace Oxrun\Command\User;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PasswordCommandTest extends TestCase
{
    /**
     * Preparation
     *
     * @return void
     */
    protected function setUp()
    {
        // TODO - insert user
    }

    /**
     * Cleanup
     */
    public static function tearDownAfterClass()
    {
        // TODO - delete user
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        //$db->execute("DELETE FROM oxuser WHERE ...");
    }
    
    public function testExecute()
    {
        $app = new Application();
        $app->add(new PasswordCommand());

        $command = $app->find('user:password');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'username' => 'info@oxid-esales.com',
                'password' => 'thenewpassword'
            )
        );

        $this->assertContains('New password set.', $commandTester->getDisplay());

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'username' => 'doesnotexists@example.com',
                'password' => 'thenewpassword'
            )
        );

        $this->assertContains('User does not exist.', $commandTester->getDisplay());
    }
}