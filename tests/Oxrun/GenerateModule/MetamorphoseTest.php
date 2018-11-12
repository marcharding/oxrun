<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 12.11.18
 * Time: 15:10
 */

namespace Oxrun\GenerateModule\Test;

use org\bovigo\vfs\vfsStream;
use Oxrun\GenerateModule\Metamorphose;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class MetamorphoseTest
 * @package Oxrun\GenerateModule\Test
 */
class MetamorphoseTest extends TestCase
{
    /**
     *
     */
    public function testActivateReadMeFile()
    {
        //Arrange
        $moduleDir = vfsStream::setup('root', 444, [
            "README.md" => "Redme is for github",
            "README_MODULE.md" => "Redme is for Module",
            "EXPECT_README.md" => "Redme is for Module",
        ])->url();

        $metamorphose = new Metamorphose($moduleDir);

        //Act
        $actual = $metamorphose->ReadMe();

        //Assert
        $this->assertInstanceOf(Metamorphose::class, $actual);
        $this->assertFileExists($moduleDir."/README.md");
        $this->assertFileEquals($moduleDir."/EXPECT_README.md", $moduleDir."/README.md");
    }

    public function testHasNotReadMeFiles(){
        //Arrange
        $moduleDir = vfsStream::setup('root', 444, [])->url();
        $metamorphose = new Metamorphose($moduleDir);

        //Assert
        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Archive has\'t {$moduleDir}/README_MODULE.md");

        //Act
        $metamorphose->ReadMe();
    }

    public function testReplacePlaceholder()
    {
        //Arrange
        $moduleDir = vfsStream::setup('root', 444, [
            "Level1.md" => "Placeholder on <LEVEL1>",
            "Expect_Level1.md" => "Placeholder on OK on L1",
            "Level2" => [
                "Level2a.md" => "Placeholder on <LEVEL2>",
                "Expect_Level2a.md" => "Placeholder on OK in L2",

                "Level2b.md" => "Placeholder on <LEVEL2>, too",
                "Expect_Level2b.md" => "Placeholder on OK in L2, too",
            ],
        ])->url();

        $metamorphose = new Metamorphose($moduleDir);

        //Act
        $actual = $metamorphose->Replacement(['<LEVEL1>', '<LEVEL2>'], ['OK on L1', 'OK in L2']);

        //Assert
        $this->assertFileEquals($moduleDir."/Expect_Level1.md", $moduleDir."/Level1.md");
        $this->assertFileEquals($moduleDir."/Level2/Expect_Level2a.md", $moduleDir."/Level2/Expect_Level2a.md");
        $this->assertFileEquals($moduleDir."/Level2/Expect_Level2b.md", $moduleDir."/Level2/Expect_Level2b.md");
        $this->assertInstanceOf(Metamorphose::class, $actual);
    }
}
