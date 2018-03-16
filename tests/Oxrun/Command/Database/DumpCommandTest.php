<?php

namespace Oxrun\Command\Database;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DumpCommandTest extends TestCase
{
    /**
     * DB execute test
     *
     * @return void
     */
    public function testExecute()
    {
        $app = new Application();
        $app->add(new DumpCommand());

        $command = $app->find('db:dump');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains('DROP TABLE IF EXISTS `oxacceptedterms`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxaccessoire2article`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxactions`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxaddress`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxarticles`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxattribute`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxcategories`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxconfig`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxorder`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxorderarticles`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxuser`;', $commandTester->getDisplay());
        $this->assertContains('DROP TABLE IF EXISTS `oxvendor`;', $commandTester->getDisplay());

        $path = tempnam(sys_get_temp_dir(), 'oxrun_db_import_test');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--file' => $path
            )
        );

        $dump = file_get_contents($path);
        $this->assertContains('DROP TABLE IF EXISTS `oxacceptedterms`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxaccessoire2article`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxactions`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxaddress`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxarticles`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxattribute`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxcategories`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxconfig`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxorder`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxorderarticles`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxuser`;', $dump);
        $this->assertContains('DROP TABLE IF EXISTS `oxvendor`;', $dump);
    }

}