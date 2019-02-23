<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 23.02.19
 * Time: 09:41
 */

namespace Oxrun;

use org\bovigo\vfs\vfsStream;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class ApplicationTest
 *
 * @package Oxrun
 */
class ApplicationTest extends TestCase
{
    protected static $preSave = ['registry' => [], 'argv' => []];

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        if (!defined('OX_BASE_PATH')) (new Application())->bootstrapOxid(false);
        foreach (Registry::getKeys() as $key) {
            self::$preSave['registry'][$key] = Registry::get($key);
        }
        self::$preSave['argv'] = $_SERVER['argv'];
    }

    /**
     * @dataProvider yamlFiles
     */
    public function testgetYamlPathDatei($yamlFile, $expect, $yamlBaseDir)
    {
        //Arrange
        $directory = vfsStream::setup('installation_root_path', 755, [
            'source' => [
                'bootstrap.php' => '<?php //OX_BASE_PATH',
                'oxrun_otherplace.yml' => $expect,
            ],
            'vendor' => [],
            'oxrun_config' => [
                'command_config.yml'  => $expect,
                'command_config.yaml' => $expect,
                'folder' => [
                    'path_config.yml'  => $expect,
                ]
            ]
        ]);
        $_SERVER['argv'] = ['', '--shopDir', $directory->getChild('source')->url()];

        $application = new Application();
        $application->bootstrapOxid();

        //Act
        $actual = $application->getYaml($yamlFile, $yamlBaseDir);

        //Assert
        $this->assertEquals($expect, $actual);
    }

    /**
     * @return array
     */
    public function yamlFiles()
    {
        return [
            'Stuffix .yml' => ['command_config.yml', 'yamlconf: 1', ''],
            'Stuffix .yaml' => ['command_config.yaml', 'yamlconf: 1', ''],
            'Relativ Path' => ['folder/path_config.yml', 'yamlconf: path', ''],
            'Other Place' => ['oxrun_otherplace.yml', 'yamlconf: 1', 'vfs://installation_root_path/source/'],
            'No File Found' => ['xymz.yml', 'xymz.yml', ''],
            'Is not YamlFile' => ['other_string', 'other_string', ''],
        ];
    }

    public function testSwitchShop()
    {
        //Arrange
        $application = new Application();
        Registry::set(ConfigFile::class, new ConfigFile(OX_BASE_PATH . "config.inc.php"));
        Registry::set('unbekannte_class', new \stdClass());

        //Act
        $application->switchToShopId(1);

        //Assert
        $this->assertEquals(1, $_GET['shp']);
        $this->assertEquals(1, $_GET['actshop']);
        $this->assertNotContains( 'unbekannte_class', Registry::getKeys());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed to switch to subshop id 4 Calculate ID: 1 Config ShopId: 1
     */
    public function testShopNotExitsToSwitch()
    {
        //Arrange
        $application = new Application();
        Registry::set(ConfigFile::class, new ConfigFile(OX_BASE_PATH . "config.inc.php"));

        //Act && Assert
        $application->switchToShopId(4);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        unset($_SERVER['argv']);

        //Clear Cache form switch Shop function
        $registryKeys = \OxidEsales\Eshop\Core\Registry::getKeys();
        foreach ($registryKeys as $key) {
            \OxidEsales\Eshop\Core\Registry::set($key, null);
        }
        unset($_GET['shp']);
        unset($_GET['actshop']);
        unset($_SESSION);
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass()
    {
        foreach ( self::$preSave['registry'] as $key => $class) {
            Registry::set($key, $class);
        }

        $_SERVER['argv'] = self::$preSave['argv'];

        self::$preSave = [];
    }
}
