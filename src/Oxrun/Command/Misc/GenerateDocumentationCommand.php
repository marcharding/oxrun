<?php

namespace Oxrun\Command\Misc;

use Oxrun\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateDocumentationCommand
 * @package Oxrun\Command\Misc
 */
class GenerateDocumentationCommand extends Command
{

    protected $skipCommands = array('help');

    protected $skipLines = array(
        '* Aliases: <none>',
        '* Is multiple: no',
        '* Shortcut: <none>',
        '* Default: `NULL`',
        '* Is required: yes',
        '* Is array: no',
        '* Is required: no',
        '* Accept value: yes',
    );

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('misc:generate:documentation')
            ->setDescription('Generate a raw command documentation of the available commands');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->skipLines = array_map(
            function ($item) {
                return $item . PHP_EOL;
            },
            $this->skipLines
        );

        $availableCommands = array_keys($this->getApplication()->all());
        $availableCommands = array_filter(
            $availableCommands, function ($commandName) {
                if (in_array($commandName, $this->skipCommands)) {
                    return false;
                }
                return true;
            }
        );

        $command = $this->getApplication()->find('help');
        $commandTester = new CommandTester($command);

        foreach ($availableCommands as $commandName) {
            $commandTester->execute(
                array(
                    'command' => $command->getName(),
                    'command_name' => $commandName,
                    '--format' => 'md'
                )
            );
            $commandOutput = $commandTester->getDisplay();
            $commandOutput = substr($commandOutput, 0, strpos($commandOutput, '**help:**'));
            $commandOutput = str_replace($this->skipLines, '', $commandOutput);

            $currentCommand = $this->getApplication()->find($commandName);
            if (count($currentCommand->getDefinition()->getOptions()) < 8) {
                $commandOutput = str_replace('### Options:', '', $commandOutput);
            }

            $commandOutput = trim($commandOutput);
            $output->writeLn($commandOutput . PHP_EOL);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }
}
