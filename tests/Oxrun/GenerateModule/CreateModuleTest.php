<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 12.11.18
 * Time: 10:33
 */

namespace Oxrun\GenerateModule\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use org\bovigo\vfs\vfsStream;
use Oxrun\GenerateModule\CreateModule;
use Oxrun\GenerateModule\ModuleSpecification;
use PHPUnit\Framework\TestCase;

/**
 * Class ReplacementTest
 *
 * @package Oxrun\GenerateModule\Test
 */
class CreateModuleTest extends TestCase
{
    public function testFunctionalTestToCreateModule()
    {
        //Arrange
        $io_hd = vfsStream::setup('root', 755, [
            "oxid" => [
                "composer.json" => file_get_contents(__DIR__ . '/testData/composer_origin.json'),
                "source" => [
                    "modules" => [],
                ],
                "vendor" => [],
            ],
            "expect" => [
                "composer.json" => file_get_contents(__DIR__ . '/testData/composer_expeced.json'),
                "AllPlaceholder.txt" => $this->expectPlaceholder(),
                "README.md" => "REDME for Module\n",
            ],
        ]);

        $shopRoot = $io_hd->getChild('oxid')->getChild('source')->url();
        $expect   = $io_hd->getChild('expect')->url();

        $mockHandler = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Length' => 0], file_get_contents(__DIR__ . '/testData/OxidModuleSkeleton.zip'))
            ])
        );
        $moduleSpecification = new ModuleSpecification();
        $moduleSpecification
            ->setModuleName('Oxid Module')
            ->setVendor('tm')
            ->setAuthorName('Wobi Tester')
            ->setAuthorEmail('wobi@unitTest')
            ->setDescription('Einfacher Tect')
        ;

        $createModule = new CreateModule($shopRoot, 'TestApp', '1.0.0', $mockHandler);

        //Act
        $createModule->run('http://mockhander/test.zip', $moduleSpecification);

        //Assert
        $this->assertFileExists("$shopRoot/modules/tm/OxidModule/");
        $this->assertFileEquals("$expect/AllPlaceholder.txt", "$shopRoot/modules/tm/OxidModule/AllPlaceholder.txt");
        $this->assertFileEquals("$expect/README.md", "$shopRoot/modules/tm/OxidModule/README.md");
        $this->assertJsonFileEqualsJsonFile("$shopRoot/../composer.json", "$expect/composer.json");
    }

    protected function expectPlaceholder()
    {
        return implode(
            "\n",
            array_values([
            '<MODULE_ID>' => 'tmOxidModule',
            '<MODULE_NAME>' => 'Oxid Module',
            '<MODULE_DESCRIPTION>' => 'Einfacher Tect',
            '<VENDOR>' => 'tm',
            '<AUTHOR_NAME>' => 'Wobi Tester',
            '<AUTHOR_EMAIL>' => 'wobi@unitTest',
            '<MODULE_NAMESPACE>' => 'tm\\OxidModule',
            '<MODULE_NAMESPACE_QUOTED>' => 'tm\\\\OxidModule',
            '<COMPOSER_NAME>' => 'tm-oxid-module',])
        )."\n";
    }
}
