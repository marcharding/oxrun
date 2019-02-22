<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-02-20
 * Time: 06:24
 */

namespace Oxrun\Command\User;

use Oxrun\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class NewCommandTest
 * @package Oxrun\Command\User
 */
class NewCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new NewUserCommand());

        $command = $app->find('user:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--admin' => true,
                '--password' => 'thenewpassword',
                'username' => 'user@test.com',
            )
        );

        $this->assertContains('could be created successfully', $commandTester->getDisplay());
    }
}
