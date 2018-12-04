<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-28
 * Time: 00:32
 */

namespace Oxrun;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ShareOxrunCommandsTest
 * @package Oxrun
 */
class ShareOxrunCommandsTest extends TestCase
{
    protected $share_commands = [];

    protected $share_definitions = [];

    /**
     * @throws \Exception
     * @dataProvider dataShareCommands
     */
    public function testCheckIfAllClassExits($command_class)
    {
        $actual = class_exists($command_class);

        $this->assertTrue($actual, 'Class not found: ' . $command_class);
    }

    /**
     * @throws \Exception
     * @dataProvider dataShareDefinitions
     */
    public function testCheckIsIdCorrect($command_definition)
    {
        $this->assertRegExp('/^oxid_community\.oxrun\./', $command_definition);
    }

    protected function loadServices()
    {
        $services = __DIR__ . '/../../services.yaml';

        $symfonyContainer = new ContainerBuilder();
        $loader = new YamlFileLoader($symfonyContainer, new FileLocator());
        $loader->load($services);

        foreach ($symfonyContainer->findTaggedServiceIds('console.command') as $id => $tags) {
            $definition = $symfonyContainer->getDefinition($id);
            $this->share_commands[]    = [ $definition->getClass() ];
            $this->share_definitions[] = [ $id ];
        }

    }

    public function dataShareCommands()
    {
        $this->loadServices();
        return $this->share_commands;
    }

    public function dataShareDefinitions()
    {
        $this->loadServices();
        return $this->share_definitions;
    }
}
