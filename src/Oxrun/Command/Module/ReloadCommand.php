<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 * Date: 07.06.17
 * Time: 07:46
 */

namespace Oxrun\Command\Module;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:reload')
            ->setDescription('Deactivate and activate a module')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->addOption('force', 'f',InputOption::VALUE_NONE, 'Force reload Module');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Oxrun\Application $app */
        $app = $this->getApplication();

        $clearCommand      = $app->find('cache:clear');
        $deactivateCommand = $app->find('module:deactivate');
        $activateCommand   = $app->find('module:activate');
        
        $argvInputClearCache = $this->createInputArray($clearCommand, $input);
        $argvInputDeactivate = $this->createInputArray($deactivateCommand, $input, ['module' => $input->getArgument('module')]);
        $argvInputActivate   = $this->createInputArray($activateCommand, $input,['module' => $input->getArgument('module')]);

        if ($input->getOption('force')) {
            $argvInputClearCache->setOption('force', true);
        }

        //Run Command
        $clearCommand->execute($argvInputClearCache, $output);
        $deactivateCommand->execute($argvInputDeactivate, $output);
        $clearCommand->execute($argvInputClearCache, $output);
        $activateCommand->execute($argvInputActivate, $output);
    }

    /**
     * @param Command$command
     * @param InputInterface $input
     */
    protected function createInputArray($command, $input, $extraOption = [])
    {
        //default --shopId
        $command->getDefinition()->addOption(new InputOption('--shopId', 'm', InputOption::VALUE_REQUIRED));

        $parameters = array_merge(
            ['--shopId' => $input->getOption('shopId')],
            $extraOption
        );

        return new ArrayInput(
            $parameters,
            $command->getDefinition()
        );
    }
}
