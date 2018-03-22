<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 * Date: 07.06.17
 * Time: 07:46
 */

namespace Oxrun\Command\Module;

use Oxrun\Command\Cache\ClearCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadCommand extends Command
{

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

        $argvInputClearCache = new ArgvInput([''], $clearCommand->getDefinition());
        $argvInputDeactivate = new ArgvInput(['', $input->getArgument('module')], $deactivateCommand->getDefinition());
        $argvInputActivate   = new ArgvInput(['', $input->getArgument('module')], $activateCommand->getDefinition());

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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }
}
