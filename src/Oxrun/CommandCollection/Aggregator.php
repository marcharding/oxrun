<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-07
 * Time: 07:05
 */

namespace Oxrun\CommandCollection;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class Aggregator
 * @package Oxrun\CommandCollection
 */
abstract class Aggregator implements CompilerPassInterface
{
    /**
     * @var Definition
     */
    private $definition;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    protected $shopDir = '';

    /**
     * @var string
     */
    protected $oxrunConfigDir = '';

    /**
     * Algorithmus to find the Commands
     *
     * @return void
     */
    abstract protected function searchCommands();

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('command_container')) {
            return;
        }

        $this->definition = $container->findDefinition('command_container');
        $this->container = $container;

        $this->searchCommands();
    }

    /**
     * Add the command class to ContainerBuilder
     *
     * @param string $commandClass
     * @param null $filepath
     *
     * @return void
     */
    protected function add($commandClass, $filepath = null)
    {
        if (! $this->isCommandCompatibleClass($commandClass)) {
            return;
        }

        if ($filepath) {
            CacheCheck::addFile($filepath);
        }

        $definitionCmd = new Definition($commandClass);
        $definitionCmd->setPublic(false);

        $this->container->setDefinition($commandClass, $definitionCmd);

        $this->definition->addMethodCall('addFromDi', [new Reference($commandClass)]);
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Filter out classes with predefined criteria to be accepted as valid `Command` classes.
     *
     * A given class should match the following criteria:
     *   a) Extends `Symfony\Component\Console\Command\Command`;
     *   b) Is not `Symfony\Component\Console\Command\Command` itself.
     *
     * @param string $class
     *
     * @return boolean
     */
    private function isCommandCompatibleClass($commandClass)
    {
        try {
            new $commandClass;
        } catch (\Exception $ex) {
            echo "Error loading class $commandClass.!\n";
        }

        return is_subclass_of($commandClass, Command::class) && $commandClass !== Command::class;
    }

    /**
     * Check is right configured
     *
     * @throws \Exception
     */
    public function valid()
    {
    }

    /**
     * @param string $shopDir
     */
    public function setShopDir(string $shopDir)
    {
        $this->shopDir = $shopDir;
    }

    /**
     * @param string $oxrunConfigDir
     */
    public function setOxrunConfigDir(string $oxrunConfigDir)
    {
        $this->oxrunConfigDir = $oxrunConfigDir;
    }
}

