<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-02-20
 * Time: 05:47
 */

namespace Oxrun\Command\User;


use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class NewUserCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;

    private $inter_passwd = '';

    protected function configure()
    {
        $this->setName('user:create')
            ->setDescription('Create a new user');

        $this->addOption(
            'admin',
            'a',
            InputOption::VALUE_NONE,
            'Add admin right'
        );

        $this->addArgument(
            "username",
            InputArgument::REQUIRED,
            "Username like email adress"
        );

        $this->addOption(
            "password",
            'p',
            InputOption::VALUE_REQUIRED,
            "Password"
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $sPassword = $input->getOption('password');

        if (!empty($sPassword)) {
            return;
        }

        $question = new Question('Passwort ');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $this->inter_passwd = $helper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sUsername = $input->getArgument('username');
        $sPassword = $input->getOption('password') ? : $this->inter_passwd;

        if (empty($sPassword)) {
            throw new \Exception("Password can't not be empty");
        }

        $fields = ["oxusername" => $sUsername];

        $oxuser = \oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oxuser->setPassword($sPassword);
        $oxuser->assign($fields);

        if ($oxuser->createUser()) {
            $output->writeln("$sUsername could be created successfully");
        } else {
            $output->writeln("<error>User '$sUsername' can not be created</error>");
            exit;
        }

        if ($input->getOption('admin')) {
            /** @var \OxidEsales\Eshop\Core\Model\BaseModel $oxbase */
            $oxbase = \oxNew(\OxidEsales\Eshop\Core\Model\BaseModel::class);
            $oxbase->init($oxuser->getCoreTableName());
            $oxbase->load($oxuser->getId());
            $oxbase->assign(['oxrights' => 'malladmin']);
            $oxbase->save();
        }
    }
}