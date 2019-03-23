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
use Oxrun\CommandCollection\Aggregator;
use Oxrun\CommandCollection\CacheCheck;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class CommunityPass
 * @package Oxrun\Helper
 */
class CommunityPass extends Aggregator
{
    /**
     * @var string
     */
    protected $installed_json = '/composer/installed.json';

    /**
     * @var array
     */
    protected $ignorePackage = [
        'oxidprojects/oxrun'
    ];

    /**
     * @inheritDoc
     */
    protected function getPassName()
    {
        return 'community';
    }

    /**
     * @throws \Exception
     */
    public function valid()
    {
        if (!file_exists($this->getInstalledJsonPath())) {
            throw new \Exception('File not found: ' . $this->installed_json . '. (usage: composer install)');
        }
    }

    /**
     * Algorithmus to find the Commands
     *
     * @return void
     * @throws \Exception
     */
    protected function searchCommands()
    {
        $this->findServicesYaml();

        $taggedServices = $this->getContainer()->findTaggedServiceIds('console.command');

        foreach ($taggedServices as $id => $tags) {
            $definition = $this->getContainer()->findDefinition($id);
            $this->addDefinition($id, $definition);
        }
    }

    /**
     * @return void
     */
    protected function findServicesYaml()
    {
        $oxid_vendor = $this->shopDir . '/../vendor/';

        $loader = new YamlFileLoader($this->getContainer(), new FileLocator());

        $installed_json = $this->getInstalledJsonPath();

        CacheCheck::addFile($installed_json);

        $localRepository = new InstalledFilesystemRepository(new JsonFile($installed_json));

        $packages = $localRepository->getPackages();

        foreach ($packages as $package) {
            $package_name = $package->getName();

            if (in_array($package_name, $this->ignorePackage)) {
                continue;
            }

            $serviceFile = $oxid_vendor . $package_name . '/services.yaml';
            if (file_exists($serviceFile)) {
                CacheCheck::addFile($serviceFile);
                $loader->load($serviceFile);
            }
        }
    }

    /**
     * @return string
     */
    protected function getInstalledJsonPath()
    {
        $installed_json = $this->shopDir . '/../vendor/' . $this->installed_json;

        return $installed_json;
    }
}
