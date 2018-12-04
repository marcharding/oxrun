<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-30
 * Time: 15:54
 */

namespace Oxrun\CommandCollection;


use Oxrun\Command\EnableInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnableAdapter
 *
 * An adapter to keep all commands compatible with other tools
 *
 * @package Oxrun\CommandCollection
 */
class EnableAdapter extends Command
{
    /**
     * @var Command|EnableInterface
     */
    private $command;

    /**
     * EnableAdapter constructor.
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $dbconnect = false;

        if ($this->command instanceof \Oxrun\Command\EnableInterface) {
            $dbconnect = $this->command->needDatabaseConnection();
        } elseif (false == $this->command->isEnabled()) {
            return false;
        }

        return $this->command->getApplication()->bootstrapOxid($dbconnect);
    }

    /*
     *  ------ Pass methods -> real command ------
     */

    /**
     * @inheritDoc
     */
    public function ignoreValidationErrors()
    {
        $this->command->ignoreValidationErrors();
    }

    public function setApplication(Application $application = null)
    {
        $this->command->setApplication($application);
    }

    public function setHelperSet(HelperSet $helperSet)
    {
        $this->command->setHelperSet($helperSet);
    }

    /**
     * @inheritDoc
     */
    public function getHelperSet()
    {
        return $this->command->getHelperSet();
    }

    /**
     * @inheritDoc
     */
    public function getApplication()
    {
        return $this->command->getApplication();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->command->configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->command->execute($input, $output);
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->command->interact($input, $output);
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->command->initialize($input, $output);
    }

    /**
     * @inheritDoc
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        return $this->command->run($input, $output);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        return $this->command->setCode($code);
    }

    /**
     * @inheritDoc
     */
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        return $this->command->mergeApplicationDefinition($mergeArgs);
    }

    /**
     * @inheritDoc
     */
    public function setDefinition($definition)
    {
        return $this->command->setDefinition($definition);
    }

    /**
     * @inheritDoc
     */
    public function getDefinition()
    {
        return $this->command->getDefinition();
    }

    /**
     * @inheritDoc
     */
    public function getNativeDefinition()
    {
        return $this->command->getNativeDefinition();
    }

    /**
     * @inheritDoc
     */
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        return $this->command->addArgument($name, $mode, $description, $default);
    }

    /**
     * @inheritDoc
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        return $this->command->addOption($name, $shortcut, $mode, $description, $default);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->command->setName($name);
    }

    /**
     * @inheritDoc
     */
    public function setProcessTitle($title)
    {
        return $this->command->setProcessTitle($title);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->command->getName();
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        return $this->command->setDescription($description);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->command->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function setHelp($help)
    {
        return $this->command->setHelp($help);
    }

    /**
     * @inheritDoc
     */
    public function getHelp()
    {
        return $this->command->getHelp();
    }

    /**
     * @inheritDoc
     */
    public function getProcessedHelp()
    {
        return $this->command->getProcessedHelp();
    }

    /**
     * @inheritDoc
     */
    public function setAliases($aliases)
    {
        return $this->command->setAliases($aliases);
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return $this->command->getAliases();
    }

    /**
     * @inheritDoc
     */
    public function getSynopsis($short = false)
    {
        return $this->command->getSynopsis($short);
    }

    /**
     * @inheritDoc
     */
    public function addUsage($usage)
    {
        return $this->command->addUsage($usage);
    }

    /**
     * @inheritDoc
     */
    public function getUsages()
    {
        return $this->command->getUsages();
    }

    /**
     * @inheritDoc
     */
    public function getHelper($name)
    {
        return $this->command->getHelper($name);
    }

    /**
     * @inheritDoc
     */
    public function asText()
    {
        return $this->command->asText();
    }

    /**
     * @inheritDoc
     */
    public function asXml($asDom = false)
    {
        return $this->command->asXml($asDom);
    }
}
