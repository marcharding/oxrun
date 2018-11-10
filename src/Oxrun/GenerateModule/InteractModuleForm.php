<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 11:31
 */

namespace Oxrun\GenerateModule;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class InteractModuleForm
 *
 * @package Oxrun\GenerateModule
 */
class InteractModuleForm
{
    /**
     * InteractModuleForm constructor.
     */
    private $helper;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * InteractModuleForm constructor.
     *
     * @param QuestionHelper $helper
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private $moduleSpecification;

    /**
     * InteractModuleForm constructor.
     *
     * @param ModuleSpecification $moduleSpecification
     * @param QuestionHelper $helper
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(ModuleSpecification $moduleSpecification, QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $this->moduleSpecification = $moduleSpecification;
        $this->helper = $helper;
        $this->input = $input;
        $this->output = $output;
    }

    public static function addCommandOptions(Command $command)
    {
        $command
            ->addOption('name', '', InputOption::VALUE_REQUIRED, 'Module name')
            ->addOption('vendor', '', InputOption::VALUE_REQUIRED, 'Vendor')
            ->addOption('description', '', InputOption::VALUE_REQUIRED, 'Description of your Module: OXID eShop Module ...')
            ->addOption('author', '', InputOption::VALUE_REQUIRED, 'Author of Module')
            ->addOption('email', '', InputOption::VALUE_REQUIRED, 'Email of Author')
        ;
    }

    public function askModuleName()
    {
        $moduleName = $this->askQuestion('name','Module name: ','');
        $this->moduleSpecification->setModuleName($moduleName);

        return $this;
    }

    public function askVendor()
    {
        $vendor = $this->askQuestion('vendor','Vendor: ','');
        $this->moduleSpecification->setVendor($vendor);

        return $this;
    }

    public function askDescription()
    {
        $description = $this->askQuestion('description','Description: OXID eShop Module ','');
        $this->moduleSpecification->setDescription($description);

        return $this;
    }

    public function askAuthor()
    {
        $author = $this->askQuestion('author','Author: ','Auto generate by oxrun');
        $this->moduleSpecification->setAuthorName($author);

        return $this;
    }

    public function askEmail()
    {
        $email = $this->askQuestion('email','Author Email: ','oxid-module-skeleton@oxid.projects.internal');
        $this->moduleSpecification->setAuthorEmail($email);

        return $this;
    }


    protected function askQuestion($optionName, $question, $default)
    {
        $value = $this->input->getOption($optionName);

        if (empty($value)) {
            $question = new Question($question, $default);
            $value = $this->helper->ask($this->input, $this->output, $question);
        }

        return $value;
    }

}