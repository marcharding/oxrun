<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-27
 * Time: 19:36
 */

namespace OxidEsales\DemoComponent\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand
 *
 * @package OxidEsales\DemoComponent\Command\HelloWorldCommand
 */
class HelloWorldCommand extends Command
{
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
