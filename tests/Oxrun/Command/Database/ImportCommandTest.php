<?php

namespace Oxrun\Command\Database;

use Oxrun\Application;
use Oxrun\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportCommandTest extends TestCase
{

    protected $sql = <<<'EOD'
DROP TABLE IF EXISTS `oxrun_db_import_test`;
CREATE TABLE `oxrun_db_import_test` (
  `OXID` char(32) NOT NULL COMMENT 'OXID id',
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='oxrun_db_import_test';
INSERT INTO `oxrun_db_import_test` VALUES ('4671a585ec23d00c682e17e5c009da65');
EOD;

    public function testExecute()
    {
        $path = tempnam(sys_get_temp_dir(), 'oxrun_db_import_test');
        file_put_contents($path, $this->sql);

        $app = new Application();
        $app->add(new ImportCommand());

        $command = $app->find('db:import');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'file' => $path,
            )
        );

        $this->assertContains("File $path is imported.", $commandTester->getDisplay());

        $app->add(new DumpCommand());

        $command = $app->find('db:dump');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains('DROP TABLE IF EXISTS `oxrun_db_import_test`;', $commandTester->getDisplay());
    }

}