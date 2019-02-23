<?php

namespace Oxrun\Command\User;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CreateUserCommand extends Command  implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Creates a new user');
    }
    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('<info>Please enter the email address of the user: </info>', '');
        $emailAddress = $helper->ask($input, $output, $question);

        $question = new Question('<info>Please enter the password for the user: </info>', '');
        $userPassword = $helper->ask($input, $output, $question);

        $question = new Question('<info>Please enter the first name of the user: </info>', '');
        $sFirstName = $helper->ask($input, $output, $question);
        $question = new Question('<info>Please enter the last name of the user: </info>', '');
        $sLastName = $helper->ask($input, $output, $question);

        $question = new ConfirmationQuestion(
            '<info>Should the user have admin rights (y/n)?</info> ',
            false,
            '/^(y|j)/i'
        );
        $isAdmin = $helper->ask($input, $output, $question);

        $output->writeln("<info>Email:</info> {$emailAddress}");
        $output->writeln("<info>Password:</info> {$userPassword}");
        $output->writeln("<info>Admin User:</info> {$isAdmin}");

        if ($emailAddress != '' && $userPassword != '') {
            $ret = $this->createUser($output, $emailAddress, $userPassword, $sFirstName, $sLastName, $isAdmin);
            if ($ret) {
                $output->writeln("<info>User created!</info>");
            }
        }
    }

    /**
     * Create new OXID user
     *
     * @param OutputInterface $output An OutputInterface instance
     * @param string  $sUser
     * @param string  $sPassword
     * @param string  $sFirstName
     * @param string  $sLastName
     * @param boolean $isAdmin
     * @return boolean
     */
    protected function createUser($output, $sUser, $sPassword, $sFirstName, $sLastName, $isAdmin = false)
    {
        /** @var \OxidEsales\Eshop\Application\Model\User $oUser */
        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        // setting values
        $oUser->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field($sUser, \OxidEsales\Eshop\Core\Field::T_RAW);
        $oUser->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field($sFirstName, \OxidEsales\Eshop\Core\Field::T_RAW);
        $oUser->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field($sLastName, \OxidEsales\Eshop\Core\Field::T_RAW);
        $oUser->setPassword($sPassword);
        $oUser->oxuser__oxactive = new \OxidEsales\Eshop\Core\Field(1, \OxidEsales\Eshop\Core\Field::T_RAW);

        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $database->startTransaction();
        try {
            $oUser->createUser();
            $oUser->load($oUser->getId());
            if ($isAdmin) {
                // we can only save oxrights field in Admin mode, so we need SQL update
                $database->execute("UPDATE oxuser SET oxrights = 'malladmin' WHERE `OXID` = '{$oUser->getId()}'");
            }
            $database->commitTransaction();
        } catch (\Exception $exception) {
            $database->rollbackTransaction();
            throw $exception;
        }
        return true;
    }
}
