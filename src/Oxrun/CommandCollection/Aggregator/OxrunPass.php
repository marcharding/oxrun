<?php

namespace Oxrun\CommandCollection\Aggregator;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:35
 */
class OxrunPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('command_container')) {
            return;
        }

        $definition = $container->findDefinition('command_container');

        $commandSourceDir          = __DIR__ . '/../../Command';
        $recursiveIteratorIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandSourceDir));
        $regexIterator             = new \RegexIterator($recursiveIteratorIterator, '/Command\.php$/');

        foreach ($regexIterator as $commandPath) {
            CacheCheck::addFile($commandPath);

            $commandClass = 'Oxrun\\Command';
            $commandClass .= str_replace(array($commandSourceDir, '/', '.php'), array('', '\\', ''), $commandPath);

            $definitionCmd = new Definition($commandClass);
            $definitionCmd->setPublic(false);
            $container->setDefinition($commandClass, $definitionCmd);

            $definition->addMethodCall('addFromDi', [new Reference($commandClass)]);;
        }
    }
}
