<?php

namespace Oxrun\Command\User;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PasswordCommandTest extends TestCase
{
    /**
     * Preparation
     *
     * @return void
     */
    protected function setUp()
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        // insert user
        $sql = "INSERT INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCUSTNR`, `OXUSTID`, `OXCOMPANY`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXADDINFO`, `OXCITY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`, `OXREGISTER`, `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXUPDATEKEY`, `OXUPDATEEXP`, `OXPOINTS`, `OXTIMESTAMP`) VALUES
        ('__dummyuser__', 1, 'malladmin', '1', 'foobar@barfoo.de', '378d25534d551e83433f532f95893d7bd6ebb2727e392da65b6cd8adb06277f577325757a21ddf6b7dd68629c1f4541b1ceeff9945dec3b7c900ba1a0a05097e', '3a4e1e271e36554a6b89ef41f8a3c544', 1, '', '', 'John', 'Doe', 'Teststrasse 10', '', '', 'Any City', 'a7c40f631fc920687.20179984', '', '90410', '217-8918712', '', 'MR', 1000, '2003-01-01 00:00:00', '2003-01-01 00:00:00', '', '', '0000-00-00', '', '', 0, 0, '2016-03-15 08:19:20')
        ON DUPLICATE KEY UPDATE oxrights='malladmin'";
        $db->execute($sql);
    }

    /**
     * Cleanup
     */
    public static function tearDownAfterClass()
    {
        // delete user
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $db->execute("DELETE FROM oxuser WHERE OXID = '__dummyuser__'");
    }
    
    public function testExecute()
    {
        $app = new Application();
        $app->add(new PasswordCommand());

        $command = $app->find('user:password');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'username' => 'foobar@barfoo.de',
                'password' => 'thenewpassword'
            )
        );

        $this->assertContains('New password set.', $commandTester->getDisplay());

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'username' => 'doesnotexists@example.com',
                'password' => 'thenewpassword'
            )
        );

        $this->assertContains('User does not exist.', $commandTester->getDisplay());
    }
}