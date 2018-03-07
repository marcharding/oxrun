<?php

namespace Oxrun\Command\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use \OxidProfessionalServices\ConfigExportImport\core\ConfigExport as ConfigExport;

/**
 * Class ExportCommand
 * @package Oxrun\Command\Config
 */
class ExportCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('config:export')
            ->setDescription('Export shop config')
            ->addOption(
                'no-debug',
                null, //can not use n
                InputOption::VALUE_NONE,
                'No debug ouput',
                null
            )
            ->addOption(
                'env',
                null,
                InputOption::VALUE_OPTIONAL,
                'Environment',
                null
            )
            ->addOption(
                'force-cleanup',
                null,
                InputOption::VALUE_OPTIONAL,
                'Force cleanup on error',
                null
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oConfigExport = new ConfigExport();
        $oConfigExport->initialize($input, $output);
        $oConfigExport->execute($input, $output);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }

}