<?php

namespace Oxrun\CommandCollection;

use Composer\Json\JsonFile;
use Composer\Repository\InstalledFilesystemRepository;
use Oxrun\CommandCollection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:34
 */
class CommunityCollection implements CommandCollection
{
    /**
     * @var string
     */
    protected $installed_json = '/composer/installed.json';

    /**
     * @param \Oxrun\Application $application
     * @throws \Exception
     */
    public function addCommandTo(\Oxrun\Application $application)
    {
        if (!$application->bootstrapOxid(false)) {
            return;
        }
        $OXID_VENDOR_PATH = $application->getShopDir() . '/../vendor/';
        $installed_json = $OXID_VENDOR_PATH . $this->installed_json ;

        if (!file_exists($installed_json)) {
            throw new \Exception('File not found: ' . $this->installed_json . '. (usage: composer install)');
        }

        $localRepository = new InstalledFilesystemRepository(new JsonFile($installed_json));
        $packages = $localRepository->getPackages();

        $symfonyContainer = new ContainerBuilder();
        $loader = new YamlFileLoader($symfonyContainer, new FileLocator());
        foreach ($packages as $package) {
            $serviceFile =  $OXID_VENDOR_PATH  . $package->getName() . '/services.yaml';
            if (file_exists($serviceFile)) {
                $loader->load($serviceFile);
            }
        }

        $errors = [];
        foreach ($symfonyContainer->findTaggedServiceIds('console.command') as $id => $tags) {
            $definition = $symfonyContainer->getDefinition($id);
            $class = '\\';
            $class .= trim($definition->getClass(), '\\');
            if (!class_exists($class)) {
                $errors[] = "Class '$class' not found in Service: $id'";
                continue;
            }
            $application->add(new $class);
        }

        if (!empty($errors)) {
            throw new \Exception('- '. implode("\n- ", $errors));
        }
    }
}
