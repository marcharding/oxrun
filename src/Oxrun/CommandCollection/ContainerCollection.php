<?php

namespace Oxrun\CommandCollection;

use Oxrun\CommandCollection;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Console\Output\ConsoleOutput;
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
     * @var bool
     */
    private $isFoundShopDir = false;

    /**
     * @param \Oxrun\Application $application
     * @throws \Exception
     */
    public function addCommandTo(\Oxrun\Application $application)
    {
        $this->isFoundShopDir = $application->bootstrapOxid(false);

        if ($this->isFoundShopDir) {
            $this->shopDir = $application->getShopDir();
            $this->template = $this->shopDir . '/../vendor/oxidprojects';
        } else {
            $this->template = sys_get_temp_dir();
        }

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
        $containerCache = new ConfigCache($this->getContainerPath(), false);
        if (!$containerCache->isFresh()) {
            $this->buildContainer($containerCache);
        }

        if (!in_array('oxidprojects\OxrunCommands', get_declared_classes())) {
            include $this->getContainerPath();
        }

        return new \oxidprojects\OxrunCommands();
    }

    /**
     * @param ConfigCache $containerCache
     */
    protected function buildContainer($containerCache)
    {
        $symfonyContainer = new ContainerBuilder();
        
        $symfonyContainer->setDefinition('command_container', new Definition(DICollection::class));

        $this->findCommands($symfonyContainer);

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

    /**
     * @param ContainerBuilder $symfonyContainer
     */
    protected function findCommands($symfonyContainer)
    {
        if ($this->isFoundShopDir) {
            try {
                //Find Community Commands
                $symfonyContainer->addCompilerPass(new Aggregator\CommunityPass($this->shopDir));

            } catch (\Exception $e) {
                $consoleOutput = new ConsoleOutput();
                $consoleOutput->writeln('<comment>Command Collection: '.$e->getMessage().'</comment>');
            }
        }

        //Find Oxrun Commands
        $symfonyContainer->addCompilerPass(new Aggregator\OxrunPass());
    }
}
