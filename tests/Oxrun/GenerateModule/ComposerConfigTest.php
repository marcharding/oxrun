<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 12.11.18
 * Time: 17:22
 */

namespace Oxrun\GenerateModule;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class ComposerConfigTest
 * @package Oxrun\GenerateModule
 */
class ComposerConfigTest extends TestCase
{

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $io_hd;

    /**
     * @dataProvider dataNamespace
     */
    public function testAddAutoloaderToConfig($namespace)
    {
        //Arrage
        $shopRoot = $this->getShopDir();
        $composerConfig = new ComposerConfig();

        //Act
        $composerConfig
            ->addAutoload($shopRoot, $namespace, $shopRoot.'/modules/tm/OxidModule');

        //Assert
        $this->assertJsonFileEqualsJsonFile(
            "$shopRoot/../composer.json",
            __DIR__ . '/testData/composer_expeced.json'
        );
    }

    public function dataNamespace()
    {
        return [
            ['tm\\OxidModule'],
            ['tm\\OxidModule\\']
        ];
    }

    public function testNotFoundComposerJson()
    {
        //Arrage
        $this->io_hd
            ->getChild('oxid')
            ->removeChild('composer.json')
        ;

        $shopRoot = $this->getShopDir();
        $composerConfig = new ComposerConfig();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^Composer.json not found/");

        //Act
        $composerConfig
            ->addAutoload($shopRoot, '', '');
    }

    public function testNotFoundModule()
    {
        //Arrage
        $this->io_hd
            ->getChild('oxid')
            ->getChild('source')
            ->removeChild('modules')
        ;
        $shopRoot = $this->getShopDir();
        $composerConfig = new ComposerConfig();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageRegExp('/^Module is not installed/');

        //Act
        $composerConfig
            ->addAutoload($shopRoot,'', $shopRoot.'/modules/tm/OxidModule');
    }

    protected function setUp()
    {
        $this->io_hd = vfsStream::setup('root', 444, [
            'oxid' => [
                'composer.json' => file_get_contents(__DIR__ . '/testData/composer_origin.json'),
                'source' => [
                    'modules' => [
                        'tm' => [
                            'OxidModule' => []
                        ]
                    ]
                ],
                'vendor' => []
            ]
        ]);
    }

    protected function getShopDir()
    {
        return $this->io_hd
            ->getChild('oxid')
            ->getChild('source')->url();
    }
}
