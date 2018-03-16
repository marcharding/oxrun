<?php

namespace Oxrun\Command\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PasswordCommand
 * @package Oxrun\Command\User
 */
class PasswordCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('user:password')
            ->setDescription('Sets a new password')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'New password');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oxUser = \oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $sql = sprintf(
            "SELECT `oxuser`.`OXID` FROM `oxuser` WHERE `oxuser`.`OXUSERNAME` = %s",
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quote($input->getArgument('username'))
        );
        $userOxid = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sql);
        if (empty($userOxid)) {
            $output->writeln('<error>User does not exist.</error>');
            return;
        }
        $oxUser->load($userOxid);
        $oxUser->setPassword($input->getArgument('password'));
        $oxUser->save();
        $output->writeln('<info>New password set.</info>');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }

}