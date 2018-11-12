<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 16:26
 */

namespace Oxrun\GenerateModule;

use Distill\Distill;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Distill\Format;

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
     * DownloadSkeleton constructor.
     * @param Client|null $Client
     */
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

        $resource = fopen($this->tempnam, 'w');
        if ($resource == false) {
            throw new FileNotFoundException("Can't open the resource at: " .$this->tempnam);
        }

        $stream   = stream_for($resource);

        $options  = [
            RequestOptions::SINK            => $stream, // the body of a response
            RequestOptions::CONNECT_TIMEOUT => 10.0,    // request
            RequestOptions::TIMEOUT         => 60.0,    // response
        ];

        $response = $this->client->get($url, $options);

        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException("Error: ".$response->getBody(), new Request('GET', $url), $response);
        }
        $stream->close();
        if ($stream->getSize() != 0) {
            fclose($resource);
        }

        if ($response->getBody()->getSize() == 0) {
            throw new BadResponseException("Error: nothing is downloaded Respone was emtpy", new Request('GET', $url), $response);
        }

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
        if (false == file_exists($destination)) {
            @mkdir($destination, 755, true);
        } else {
            throw new \Exception('The Destination exists: '.$destination);
        }

        $distill = new Distill();
        if (false == $distill->extract($this->tempnam, $destination, new Format\Simple\Zip())) {
            throw new \Exception('Can not be extract.');
        };
    }

    public function __destruct()
    {
        if (file_exists($this->tempnam)) {
            @unlink($this->tempnam);
        }
    }
}
