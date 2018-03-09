<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 * Date: 07.06.17
 * Time: 07:46
 */

namespace Oxrun\Command\Module;

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
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL, null)
            ->addArgument('module', InputArgument::REQUIRED, 'Module name');
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
        $shopId = $input->getOption('shopId');
        if ($shopId) {
            $app->switchToShopId($shopId);
        }
        $app->find('module:deactivate')->run($input, $output);
        $app->find('cache:clear')->run(new ArgvInput([]), $output);
        $app->find('module:activate')->run($input, $output);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }
}
