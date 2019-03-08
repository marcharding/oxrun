<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2018-11-27
 * Time: 19:36
 */

namespace Oxrun\CustomCommand;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand
 *
 * @package OxidEsales\DemoComponent\Command\HelloWorldCommand
 */
class IsNotACommand
{
    public function display(OutputInterface $output)
    {
        $output->writeln('Hello World');
    }
}
