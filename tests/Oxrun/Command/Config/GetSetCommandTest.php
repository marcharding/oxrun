<?php

namespace Oxrun\Command\Config;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GetSetCommandTest extends TestCase
{
    public function testExecute()
    {
        $app = new Application();
        $app->add(new SetCommand());
        $app->add(new GetCommand());

        $setCommand = $app->find('config:set');
        $getCommand = $app->find('config:get');

        $randomColumns = array(
            md5(microtime(true) . rand(1024, 2048)),
            md5(microtime(true) . rand(1024, 2048)),
            md5(microtime(true) . rand(1024, 2048))
        );
        $randomColumnsJson = json_encode($randomColumns, true);

        $commandTester = new CommandTester($setCommand);
        $commandTester->execute(
            array(
                'command' => $setCommand->getName(),
                'variableName' => 'aSortCols',
                'variableValue' => $randomColumnsJson
            )
        );

        $commandTester = new CommandTester($getCommand);
        $commandTester->execute(
            array(
                'command' => $getCommand->getName(),
                'variableName' => 'aSortCols',
            )
        );

        $this->assertContains('aSortCols has value ' . $randomColumnsJson, $commandTester->getDisplay());

        $commandTester = new CommandTester($setCommand);
        $commandTester->execute(
            array(
                'command' => $setCommand->getName(),
                'variableName' => 'bl_perfLoadAktion',
                'variableValue' => false
            )
        );

        $commandTester = new CommandTester($getCommand);
        $commandTester->execute(
            array(
                'command' => $getCommand->getName(),
                'variableName' => 'bl_perfLoadAktion',
            )
        );

        $this->assertContains('bl_perfLoadAktion has value 0', $commandTester->getDisplay());

        $commandTester = new CommandTester($setCommand);
        $commandTester->execute(
            array(
                'command' => $setCommand->getName(),
                'variableName' => 'bl_perfLoadAktion',
                'variableValue' => true
            )
        );

        $commandTester = new CommandTester($getCommand);
        $commandTester->execute(
            array(
                'command' => $getCommand->getName(),
                'variableName' => 'bl_perfLoadAktion',
            )
        );

        $this->assertContains('bl_perfLoadAktion has value 1', $commandTester->getDisplay());

        $commandTester = new CommandTester($setCommand);
        $commandTester->execute(
            array(
                'command' => $setCommand->getName(),
                'variableName' => 'iTopNaviCatCount',
                'variableValue' => 99,
                '--moduleId' => 'theme:azure'
            )
        );

        $commandTester = new CommandTester($getCommand);
        $commandTester->execute(
            array(
                'command' => $getCommand->getName(),
                'variableName' => 'iTopNaviCatCount',
                '--moduleId' => 'theme:azure'
            )
        );

        $this->assertContains('iTopNaviCatCount has value 99', $commandTester->getDisplay());

    }

}