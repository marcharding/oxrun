<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-04
 * Time: 19:09
 */

namespace Oxrun\CommandCollection\Aggregator;

use Composer\Json\JsonFile;
use Composer\Repository\InstalledFilesystemRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CommunityPass
 * @package Oxrun\Helper
 */
class CommunityPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $shopDir = '';

    /**
     * @var string
     */
    protected $installed_json = '/composer/installed.json';

    /**
     * CommunityPass constructor.
     * @param $shopDir
     */
    public function __construct($shopDir)
    {
        $this->shopDir = $shopDir;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('command_container')) {
            return;
        }

        $definition = $container->findDefinition('command_container');

        $this->findServicesYaml($container);

        $taggedServices = $container->findTaggedServiceIds('console.command');

        foreach ($taggedServices as $id => $tags) {
            $defCommand = $container->findDefinition($id);
            $defCommand->setPublic(false);

            $definition->addMethodCall('addFromDi', [new Reference($id)]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    protected function findServicesYaml(ContainerBuilder $container)
    {
        $OXID_VENDOR_PATH = $this->shopDir . '/../vendor/';
        $installed_json = $OXID_VENDOR_PATH . $this->installed_json;

        if (!file_exists($installed_json)) {
            throw new \Exception('File not found: ' . $this->installed_json . '. (usage: composer install)');
        }

        CacheCheck::addFile($installed_json);

        $localRepository = new InstalledFilesystemRepository(new JsonFile($installed_json));
        $packages = $localRepository->getPackages();

        $loader = new YamlFileLoader($container, new FileLocator());

        foreach ($packages as $package) {
            $serviceFile = $OXID_VENDOR_PATH . $package->getName() . '/services.yaml';
            if (file_exists($serviceFile)) {
                CacheCheck::addFile($serviceFile);
                $loader->load($serviceFile);
            }
        }
    }
}
