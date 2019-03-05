<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-02
 * Time: 17:50
 */

namespace Oxrun\Command;

use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Registry;
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

        $command = $app->find('misc:generate:yaml:module');

        //Arrange
        $shopDir['oxrun_config']['dev.yml'] = Yaml::dump(['blacklist' => ['1' => ['a','b','c']], 'whitelist' => ['2' => ['BleibtA']]]);
        $app->checkBootstrapOxidInclude($this->fillShopDir($shopDir)->getVirtualBootstrap());

        $moduleList = $this->prophesize(ModuleList::class);
        $moduleList->getActiveModuleInfo()->willReturn(['ModuleA' => null, 'ModuleB' => null, 'ModuleC' => null]);
        Registry::set(ModuleList::class, $moduleList->reveal());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--configfile' => 'dev',
                '--whitelist' => true,
            )
        );

        $actual = Yaml::parse(file_get_contents($app->getOxrunConfigPath() . 'dev.yml'));
        $expect = [
            'whitelist' => [
                '1' => [
                    'ModuleA',
                    'ModuleB',
                    'ModuleC',
                ],
                '2' => [
                    'BleibtA'
                ],
            ]
        ];
        $this->assertEquals($expect, $actual);
        $this->assertContains('Module saved use `oxrun module:multiactivate dev.yml`', $commandTester->getDisplay());

    }

    public function testExportBlacklist()
    {
        $app = new Application();
        $app->add(new EnableAdapter(new GenerateYamlModuleListCommand()));

        $command = $app->find('misc:generate:yaml:module');

        //Arrange
        $shopDir['oxrun_config']['dev.yaml'] = Yaml::dump(['blacklist' => ['1' => ['a','b','c']], 'whitelist' => ['2' => ['BleibtA']]]);
        $app->checkBootstrapOxidInclude($this->fillShopDir($shopDir)->getVirtualBootstrap());

        $moduleList = $this->prophesize(ModuleList::class);
        $moduleList->getDisabledModules()->willReturn(['ModuleA', 'ModuleB']);
        Registry::set(ModuleList::class, $moduleList->reveal());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--configfile' => 'dev.yaml',
                '--blacklist' => true,
            )
        );

        $actual = Yaml::parse(file_get_contents($app->getOxrunConfigPath() . 'dev.yaml'));
        $expect = [
            'blacklist' => [
                '1' => [
                    'ModuleA',
                    'ModuleB',
                ]
            ]
        ];
        $this->assertEquals($expect, $actual);
    }
}
