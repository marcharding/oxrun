<?php

namespace Oxrun\Command\User;

use Oxrun\Application;
use Oxrun\CommandCollection\EnableAdapter;
use Oxrun\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test expecting user input
 * @see http://symfony.com/doc/2.8/components/console/helpers/questionhelper.html
 */
class CreateUserCommandTest extends TestCase
{
    /**
     * Cleanup
     */
    public static function tearDownAfterClass()
    {
        // delete user
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $db->execute("DELETE FROM oxuser WHERE OXUSERNAME = 'dummyuser@oxrun.com'");
    }
    
    public function testExecute()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new CreateUserCommand()));

        $command = $app->find('user:create');

        $commandTester = new CommandTester($command);
        // Equals to a user inputting "dummyuser@oxrun.com" and hitting ENTER
        $helper = $command->getHelper('question');
        // Equals to a user inputting "Test" and hitting ENTER
        // If you need to enter a confirmation, "yes\n" will work
        $helper->setInputStream($this->getInputStream("dummyuser@oxrun.com\nsecretpass\nDummy\nUser\nyes\n"));
        // in newer Symfony versions you can just use:
        //$commandTester->setInputs(array('dummyuser@oxrun.com'));
        $commandTester->execute(
            array('command' => $command->getName())
        );

        $this->assertContains('User created', $commandTester->getDisplay());
    }

    /**
     * Get the input stream
     *
     * @param [type] $input
     * @return void
     */
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
