<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 12.11.18
 * Time: 10:32
 */

namespace Oxrun\GenerateModule;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Class CreateModule
 *
 * @package Oxrun\GenerateModule
 */
class CreateModule
{
    /**
     * @var string
     */
    private $shopdir;

    /**
     * @var string
     */
    private $appname;

    /**
     * @var string
     */
    private $version;

    /**
     * @var HandlerStack
     */
    private $mockHandler = null;

    /**
     * CreateModule constructor.
     *
     * @param string            $shopdir
     * @param string            $appname
     * @param string            $version
     * @param HandlerStack|null $mockHandler Use that for Tests
     */
    public function __construct($shopdir, $appname, $version, HandlerStack $mockHandler = null)
    {
        $this->shopdir = $shopdir;
        $this->appname = $appname;
        $this->version = $version;
        $this->mockHandler = $mockHandler;
    }

    /**
     * @param string $skeletonUri
     * @param ModuleSpecification $moduleSpecification
     *
     * @throws \Distill\Exception\IO\Input\FileEmptyException
     * @throws \Distill\Exception\IO\Input\FileFormatNotSupportedException
     * @throws \Distill\Exception\IO\Input\FileNotFoundException
     * @throws \Distill\Exception\IO\Input\FileNotReadableException
     * @throws \Distill\Exception\IO\Input\FileUnknownFormatException
     * @throws \Distill\Exception\IO\Output\TargetDirectoryNotWritableException
     */
    public function run($skeletonUri, ModuleSpecification $moduleSpecification)
    {
        $client     = $this->createHttpClient();
        $modulePath = $moduleSpecification->getDestinationPath($this->shopdir);
        $namespace  = $moduleSpecification->getNamespace();
        $placholder = $moduleSpecification->getPlaceholders();
        $values     = $moduleSpecification->getPlaceholderValues();

        $downloadSkeleton = new DownloadSkeleton($client);
        $downloadSkeleton
            ->download($skeletonUri)
            ->extractTo($modulePath);

        $metamorphose = new Metamorphose($modulePath);
        $metamorphose
            ->ReadMe()
            ->Replacement($placholder, $values)
        ;

        $composerConfig = new ComposerConfig();
        $composerConfig
            ->addAutoload($this->shopdir, $namespace, $modulePath);
    }

    /**
     * @return Client
     */
    protected function createHttpClient()
    {
        $config = [
            'timeout' => 0,
            'allow_redirects' => true,
            'headers' => ['User-Agent' => $this->appname . '/' . $this->version . ' PHP/' . PHP_VERSION]
        ];

        if ($this->mockHandler) {
            $config['handler'] = $this->mockHandler;
        }

        $client = new Client($config);

        return $client;
    }
}