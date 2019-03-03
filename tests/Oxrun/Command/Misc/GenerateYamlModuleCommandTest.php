<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-02
 * Time: 17:50
 */

namespace Oxrun\Command;

use Oxrun\Application;
use Oxrun\Command\Misc\GenerateYamlModuleListCommand;
use Oxrun\CommandCollection\EnableAdapter;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GenerateYamlModuleCommandTest
 * @package Oxrun\Command
 */
class GenerateYamlModuleCommandTest extends TestCase
{

    public function testExportWhitelist()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new GenerateYamlModuleListCommand()));

        $app->checkBootstrapOxidInclude($this->fillShopDir([])->getVirtualBootstrap());

        $command = $app->find('misc:generate:yaml:module');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--configfile' => 'dev.yml',
                '--whiteliste' => true,
            )
        );

        $this->markTestIncomplete('Assert must be write');

//        $actual = Yaml::parse(file_get_contents($app->getOxrunConfigPath() . 'dev.yml'));
//        $expect = ['config' => [
//            '1' => [
//                'varA' => 'besteht',
//                'unitVarB' => 'abcd1',
//                'unitVarC' => 'cdef1',
//            ]
//        ]];
//        $this->assertEquals($expect, $actual);
    }
//
//    public function testExportModullVariable()
//    {
//        $app = new Application();
//        $app->add(new EnableAdapter(new GenerateYamlMultiSetCommand()));
//
//        Registry::getConfig()->saveShopConfVar('str', 'unitModuleB', 'abcd1', 1, 'module:unitTest');
//        Registry::getConfig()->saveShopConfVar('str', 'unitModuleW', 'cdef1', 1, 'module:unitNext');
//
//        $app->checkBootstrapOxidInclude($this->fillShopDir([])->getVirtualBootstrap());
//
//        $command = $app->find('misc:generate:yaml:multiset');
//
//        $commandTester = new CommandTester($command);
//        $commandTester->execute(
//            array(
//                'command' => $command->getName(),
//                '--oxmodule' => 'module:unitTest, module:unitNext',
//            )
//        );
//
//        $actual = Yaml::parse(file_get_contents($app->getOxrunConfigPath() . 'dev.yml'));
//        $expect = ['config' => [
//            '1' => [
//                'unitModuleB' => [
//                    'variableType' => 'str',
//                    'variableValue' => 'abcd1',
//                    'moduleId' => 'module:unitTest'
//                ],
//                'unitModuleW' => [
//                    'variableType' => 'str',
//                    'variableValue' => 'cdef1',
//                    'moduleId' => 'module:unitNext'
//                ],
//            ]
//        ]];
//
//        $this->assertEquals($expect, $actual);
//    }
//
//    public function testExportModulVariableNameAndShop2()
//    {
//        $app = new Application();
//        $app->add(new EnableAdapter(new GenerateYamlMultiSetCommand()));
//
//        Registry::getConfig()->saveShopConfVar('str', 'unitSecondShopName', 'Mars', 2, 'module:unitMars');
//        Registry::getConfig()->saveShopConfVar('str', 'unitEgal',           'none', 2, 'module:unitMars');
//
//        $app->checkBootstrapOxidInclude($this->fillShopDir([])->getVirtualBootstrap());
//
//        $command = $app->find('misc:generate:yaml:multiset');
//
//        $commandTester = new CommandTester($command);
//        $commandTester->execute(
//            array(
//                'command' => $command->getName(),
//                '--oxvarname' => 'unitSecondShopName',
//                '--oxmodule' => 'module:unitMars',
//                '--shopId' => '2',
//            )
//        );
//
//        $actual = Yaml::parse(file_get_contents($app->getOxrunConfigPath() . 'dev.yml'));
//        $expect = ['config' => [
//            '2' => [
//                'unitSecondShopName' => [
//                    'variableType' => 'str',
//                    'variableValue' => 'Mars',
//                    'moduleId' => 'module:unitMars'
//                ]
//            ]
//        ]];
//
//        $this->assertEquals($expect, $actual);
//    }

}
