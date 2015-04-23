<?php

namespace Oxrun\Command\Cms;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 * @package Oxrun\Command\Cms
 */
class UpdateCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('cms:update')
            ->setDescription('Gets a config value')
            ->addArgument('ident', InputArgument::REQUIRED, 'Content ident')
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, 'Content title')
            ->addOption('content', null, InputOption::VALUE_OPTIONAL, 'Content body')
            ->addOption('language', null, InputOption::VALUE_REQUIRED, 'Content language')
            ->addOption('active', null, InputOption::VALUE_REQUIRED, 'Content active');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oxContent = oxNew('oxcontent');
        $oxContent->loadByIdent($input->getArgument('ident'));

        if (!$oxContent) {
            $output->writeLn("<error>Content with ident {$input->getArgument('ident')} not found.</error>");
            return;
        }

        if ($input->getOption('language') !== false) {
            $language = $input->getOption('language');
        } else {
            $language = \oxRegistry::getLang()->getBaseLanguage();
        }

        $oxContent->setLanguage($language);

        if ($input->getOption('title')) {
            $oxContent->oxcontents__oxtitle = new \oxField($input->getOption('title'));
        }

        if ($input->getOption('content')) {
            if (is_file(getcwd() . '/' . $input->getOption('content'))) {
                $content = file_get_contents(getcwd() . '/' . $input->getOption('content'));
            } else {
                $content = $input->getOption('content');
            }
            $oxContent->oxcontents__oxcontent = new \oxField($content);
        }

        if ($input->getOption('active')) {
            $oxContent->oxcontents__oxactive = new \oxField($input->getOption('active'));
        }
        if ($oxContent->save()) {
            $output->writeLn("<info>Content with ident {$input->getArgument('ident')} updated.</info>");
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