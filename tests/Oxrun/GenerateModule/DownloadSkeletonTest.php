<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 16:26
 */

namespace Oxrun\GenerateModule;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class DownloadSkeletonTest
 *
 * @package Oxrun\GenerateModule
 */
class DownloadSkeletonTest extends TestCase
{
    /**
     * @var DownloadSkeleton
     */
    private $downloadSkeleton;

    /**
     * @var Client|ObjectProphecy
     */
    private $client;

    /**
     * @var array
     */
    private $toUnlink = [];

    protected function setUp()
    {
        $this->client = $this->prophesize(Client::class);
        $this->downloadSkeleton = new DownloadSkeleton($this->client->reveal());
    }

    public function testDownload()
    {
        //Arrage
        $url = "https://localhost/test.zip";
        $response = new Response(200, [], 'Content of /testData/ModuleArchive.zip');

        //Assert
        $this->client->get(Argument::is($url),Argument::any())->willReturn($response)->shouldBeCalled();

        //Act
        $actual = $this->downloadSkeleton->download($url);

        //Assert
        $this->assertInstanceOf(DownloadSkeleton::class, $actual);
    }

    public function testDownloadNot200Status()
    {
        //Arrage
        $url = "https://localhost/test.zip";
        $response = new Response(400, [], 'Content of /testData/ModuleArchive.zip');

        $this->client->get(Argument::is($url),Argument::any())->willReturn($response);

        //Assert
        $this->expectException(BadResponseException::class);

        //Act
        $this->downloadSkeleton->download($url);
    }

    public function testEmptyDownload()
    {
        //Arrage
        $url = "https://localhost/test.zip";
        $response = new Response(400, [], '');

        $this->client->get(Argument::is($url),Argument::any())->willReturn($response);

        //Assert
        $this->expectException(BadResponseException::class);

        //Act
        $this->downloadSkeleton->download($url);
    }

    /**
     * @dataProvider dataZips
     */
    public function testExtractDownload($zipname)
    {
        //Arrage
        $url = "https://localhost/test.zip";
        $tempnam = sys_get_temp_dir() . '/OXID_Module/'. basename($zipname, '.zip');
        $this->toUnlink[] = dirname($tempnam);

        $this->client->get(Argument::is($url),Argument::any())->will(function ($args) use ($zipname) {
            /** @var \GuzzleHttp\Psr7\Stream $stream */
            $contents = file_get_contents(__DIR__ . '/testData/'.$zipname);
            $stream = $args[1][RequestOptions::SINK];
            $stream->write($contents);

            return new Response(200, [], $contents);
        });


        //Act
        $this->downloadSkeleton
            ->download($url)
            ->extractTo($tempnam);


        //Assert
        $this->assertFileExists($tempnam . '/' . 'metadata.php');
    }

    public function testDownloadedArchiveIsNotAoxidModule()
    {
        //Arrage
        $url = "https://localhost/test.zip";
        $tempnam = sys_get_temp_dir() . '/OXID_Module/NotOxidModuleArchiv';
        $this->toUnlink[] = dirname($tempnam);

        $this->client->get(Argument::is($url),Argument::any())->will(function ($args)  {
            /** @var \GuzzleHttp\Psr7\Stream $stream */
            $contents = file_get_contents(__DIR__ . '/testData/NotOxidModuleArchiv.zip');
            $stream = $args[1][RequestOptions::SINK];
            $stream->write($contents);

            return new Response(200, [], $contents);
        });

        //Assert
        $this->expectExceptionMessage('Archive is not a OXID Module Archive');
        $this->expectException(\Exception::class);

        //Act
        $this->downloadSkeleton
            ->download($url)
            ->extractTo($tempnam);
    }

    /**
     * @return array
     */
    public function dataZips()
    {
        return [
            ["ModuleArchiv.zip"],
            ["ModuleTree.zip"],
        ];

}

    /**
     * This method is called after each test.
     */
    protected function tearDown()
    {
        foreach ($this->toUnlink as $file) {
            exec('rm -rf '. $file . ' 2>&1',$output, $code);
        }
        $this->toUnlink = [];
    }
}
