<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 06.03.19
 * Time: 12:23
 */

namespace Oxrun\Command\Misc;

use Oxrun\Application;
use Oxrun\CommandCollection\EnableAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateDocumentationCommandTest
 * @package Oxrun\Command\Misc
 */
class GenerateDocumentationCommandTest extends TestCase
{
    public function testExcute()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new GenerateDocumentationCommand()));

        $command = $app->find('misc:generate:documentation');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );

        $this->assertContains('Available commands', $commandTester->getDisplay());
    }
}
