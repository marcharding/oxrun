<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-27
 * Time: 19:36
 */

namespace AnyNamespace\ModuleCommand;

use Oxrun\Command\EnableInterface;
use Oxrun\Traits\NoNeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ModuleCommand
 *
 * @package AnyNamespace\ModuleCommand
 */
class ModuleCommand extends Command implements EnableInterface
{
    /**
     * This command does not need a database
     */
    use NoNeedDatabase;

    protected function configure()
    {
        $this
            ->setName('demo-component:say-hello')
            ->setDescription('Hello World Command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello World');
    }
}
