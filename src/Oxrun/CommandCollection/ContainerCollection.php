<?php

namespace Oxrun\CommandCollection;

use Oxrun\CommandCollection;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 27.11.18
 * Time: 07:34
 */
class ContainerCollection implements CommandCollection
{
    /**
     * @var string
     */
    private $shopDir = '';

    /**
     * @var string
     */
    private $template = '';

    /**
     * @param \Oxrun\Application $application
     * @throws \Exception
     */
    public function addCommandTo(\Oxrun\Application $application)
    {
        if (!$application->bootstrapOxid(false)) {
            return;
        }

        $this->shopDir = $application->getShopDir();
        $this->template = $this->shopDir . '/../vendor/oxidprojects';

        /** @var DICollection $commandContainer */
        $commandContainer = $this->getContainer()->get('command_container');
        $commandContainer->addCommandTo($application);
    }

    /**
     * @return Container
     * @throws \Exception
     */
    protected function getContainer()
    {
        $containerCache = new ConfigCache($this->getContainerPath(), true);
        if (!$containerCache->isFresh()) {
            $this->buildContainer($containerCache);
        }

        include_once $this->getContainerPath();

        return new \oxidprojects\OxrunCommands();
    }

    /**
     * @param ConfigCache $containerCache
     */
    protected function buildContainer($containerCache)
    {
        $symfonyContainer = new ContainerBuilder();
        
        $symfonyContainer->setDefinition('command_container', new Definition(DICollection::class));

        //Find any Command
        $symfonyContainer->addCompilerPass(new Aggregator\CommunityPass($this->shopDir));
        $symfonyContainer->addCompilerPass(new Aggregator\OxrunPass());
        
        $symfonyContainer->compile();

        $phpDumper = new PhpDumper($symfonyContainer);

        $containerCache->write(
            $phpDumper->dump([
                'class' => 'OxrunCommands',
                'namespace' => 'oxidprojects',
            ]),
            Aggregator\CacheCheck::getResource()
        );
    }

    /**
     * @return string
     */
    protected function getContainerPath()
    {
        return $this->template . '/OxrunCommands.php';
    }
}
