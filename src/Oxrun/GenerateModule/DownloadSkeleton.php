<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 16:26
 */

namespace Oxrun\GenerateModule;

use Distill\Distill;
use Distill\Format;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class DownloadSkeleton
 * @package Oxrun\GenerateModule
 */
class DownloadSkeleton
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $tempnam;

    /**
     * @var array
     */
    private $unlink = [];

    /**
     * DownloadSkeleton constructor.
     * @param Client|null $Client
     */
    private $zipfilename;

    public function __construct(Client $Client = null)
    {
        $this->client = $Client;
    }

    /**
     * @param $url
     */
    public function download($url)
    {
        $this->tempnam = tempnam(sys_get_temp_dir(), uniqid(strftime('%G-%m-%d')));
        $this->unlink[] = $this->tempnam;

        $resource = fopen($this->tempnam, 'w');
        if ($resource == false) {
            throw new FileNotFoundException("Can't open the resource at: " . $this->tempnam);
        }

        $stream = stream_for($resource);

        $options = [
            RequestOptions::SINK => $stream, // the body of a response
            RequestOptions::CONNECT_TIMEOUT => 10.0,    // request
            RequestOptions::TIMEOUT => 60.0,    // response
        ];

        $response = $this->client->get($url, $options);


        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException("Error: " . $response->getBody(), new Request('GET', $url), $response);
        }

        if ($response->getBody()->getSize() == 0) {
            throw new BadResponseException("Error: nothing is downloaded Respone was emtpy", new Request('GET', $url), $response);
        }

        if ($stream->getSize() != 0) {
            fclose($resource);
        }
        $stream->close();

        return $this;
    }

    /**
     * @param $destination
     * @throws \Distill\Exception\IO\Input\FileEmptyException
     * @throws \Distill\Exception\IO\Input\FileFormatNotSupportedException
     * @throws \Distill\Exception\IO\Input\FileNotFoundException
     * @throws \Distill\Exception\IO\Input\FileNotReadableException
     * @throws \Distill\Exception\IO\Input\FileUnknownFormatException
     * @throws \Distill\Exception\IO\Output\TargetDirectoryNotWritableException
     * @throws \Exception
     */
    public function extractTo($destination)
    {
        if (file_exists($destination)) {
            throw new \Exception('The Destination exists: ' . $destination);
        }

        $downloadFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(strftime('%G-%m-%d')) . '-dwn';
        @mkdir($downloadFolder, 0755, true);
        $this->unlink[] = $downloadFolder;

        $basedir = \dirname($destination);
        if (false == file_exists($basedir)) {
            @mkdir($downloadFolder, 0755, true);
        }

        $distill = new Distill();
        if (false == $distill->extract($this->tempnam, $downloadFolder, new Format\Simple\Zip())) {
            throw new \Exception('Can not be extract.');
        };

        $filesystem = new Filesystem();
        $finder = new Finder();
        $finder->files()->name('metadata.php')->in($downloadFolder);

        if ($finder->count() == 0) {
            throw new \Exception("Archive is not a OXID Module Archive");
        }
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $zipfolder = $file->getPath();
            $filesystem->rename($zipfolder, $destination);
            break;
        }
    }

    public function __destruct()
    {
        foreach ($this->unlink as $file) {
            if (file_exists($file)) {
                exec("rm -rf $file 2>&1" );
            }
        }
    }
}
