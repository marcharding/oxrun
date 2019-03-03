<?php

namespace Oxrun;

use org\bovigo\vfs\vfsStream;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private $template = [
        'source' => [ 'bootstrap.php' => '<?php //OX_BASE_PATH' ],
        'vendor' => [],
        'oxrun_config' => []
    ];

    private $vfsStreamDirectory = null;

    /**
     * Fill the simulate shop dir with your files.
     *
     * @param array $fill
     *
     * @return $this
     */
    protected function fillShopDir(array $fill)
    {
        $this->vfsStreamDirectory = array_merge_recursive($this->template, $fill);

        return $this;
    }

    /**
     * Get the new ['--shopDir' => $this->fillShopDir([...])->getVfsStreamUrl()]
     *
     * @return string
     */
    protected function getVfsStreamUrl()
    {
        if ($this->vfsStreamDirectory === null) {
            $this->vfsStreamDirectory = $this->template;
        }
        return vfsStream::setup('installation_root_path', 755, $this->vfsStreamDirectory)
            ->getChild('source')
            ->url();
    }

    /**
     * @return string
     */
    protected function getVirtualBootstrap()
    {
        return $this->getVfsStreamUrl() . '/bootstrap.php';
    }

    protected function tearDown()
    {
        $this->vfsStreamDirectory == null;
        parent::tearDown();
    }


}
