<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-05
 * Time: 07:07
 */

namespace Oxrun\Tests\CommandCollection;

use Oxrun\Application;
use Oxrun\CommandCollection\Aggregator;
use Oxrun\CommandCollection\CacheCheck;
use Oxrun\CommandCollection\CommandFinder;
use Oxrun\CommandCollection\ContainerCollection;
use Oxrun\CommandCollection\EnableAdapter;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ContainerCollectionTest
 * @package Oxrun\CommandCollection
 */
class ContainerCollectionTest extends \Oxrun\TestCase
{
    /**
     * @var Application|ObjectProphecy|MethodProphecy
     */
    private $app;

    /**
     * @var string
     */
    private $oxid_fs_source;

    /**
     * @var CommandFinder|ObjectProphecy|MethodProphecy
     */
    private $commandFinder;

    /**
     * @var Aggregator|ObjectProphecy|MethodProphecy
     */
    private $pass;

    /**
     * @var Aggregator|ObjectProphecy|MethodProphecy
     */
    private $passNeedDir;

    public function testAddCommandWithoutFindBootstrap()
    {
        //Arrgange
        $this->app->bootstrapOxid(Argument::any())->willReturn(false);
        $this->app->getShopDir()->willReturn('');

        //Assert
        $this->app->add(Argument::type(EnableAdapter::class))->shouldBeCalled();

        //Act
        $containerCollection = new ContainerCollection($this->commandFinder->reveal());
        $containerCollection->addCommandTo($this->app->reveal());
    }

    public function testCreateContainerFileIntoComposerVendor()
    {
        //Arrgange
        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->app->getShopDir()->willReturn($this->oxid_fs_source);
        $this->app->getOxrunConfigPath()->willReturn($this->oxid_fs_source.'/../oxrun_config/');

        //Act
        $containerCollection = new ContainerCollection($this->commandFinder->reveal());
        $containerCollection->addCommandTo($this->app->reveal());

        //Arrange
        $this->assertFileExists($this->oxid_fs_source.'/../vendor/oxidprojects/OxrunCommands.php');
        $this->assertFileExists($this->oxid_fs_source.'/../vendor/oxidprojects/OxrunCommands.php.meta');
    }

    public function testAlwayFindOxRunCommandIfThrowException()
    {
        //Arrgange
        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->app->getShopDir()->willReturn($this->oxid_fs_source);
        $this->app->getOxrunConfigPath()->willReturn($this->oxid_fs_source.'/../oxrun_config/');
        $bufferedOutput = new BufferedOutput();

        $this->passNeedDir->valid()->willThrow(new \Exception('something is wrong'));

        //Assert
        $this->app->add(Argument::type(EnableAdapter::class))->shouldBeCalled();

        //Act
        $containerCollection = new ContainerCollection($this->commandFinder->reveal(), $bufferedOutput);
        $containerCollection->addCommandTo($this->app->reveal());

        //Assert
        $this->assertEquals('Own commands error: something is wrong'.PHP_EOL, $bufferedOutput->fetch());
    }

    protected function setUp()
    {
        $this->app = $this->prophesize(Application::class);
        $this->app->bootstrapOxid(Argument::any())->willReturn(true);
        $this->app->add(Argument::type(EnableAdapter::class))->willReturn('');

        $this->commandFinder = $this->prophesize(CommandFinder::class);

        $this->pass = $this->prophesize(Aggregator::class);
        $this->pass->setShopDir(Argument::any())->willReturn();
        $this->pass->setOxrunConfigDir(Argument::any())->willReturn();
        $this->pass->setConsoleOutput(Argument::any())->willReturn();
        $this->pass->valid()->willReturn();
        $this->pass->process(Argument::any())->willReturn();

        $this->passNeedDir = $this->prophesize(Aggregator::class);
        $this->passNeedDir->setShopDir(Argument::any())->willReturn();
        $this->passNeedDir->setOxrunConfigDir(Argument::any())->willReturn();
        $this->passNeedDir->setConsoleOutput(Argument::any())->willReturn();
        $this->passNeedDir->valid()->willReturn();
        $this->passNeedDir->process(Argument::any())->willReturn();

        $command = $this->prophesize(Command::class);
        $unitCommand = (new UnitCommand())->setCommandName(get_class($command->reveal()));

        $this->commandFinder->getPass()->willReturn([$this->pass->reveal(), $unitCommand]);
        $this->commandFinder->getPassNeedShopDir()->willReturn([$this->passNeedDir->reveal()]);

        $this->mockShopDir();
    }


    protected function tearDown()
    {
        CacheCheck::clean();
        parent::tearDown();
    }

    protected function mockShopDir() {

        $oxid_fs['vendor']['composer']['installed.json'] = file_get_contents(self::getTestData('installed_one_package.json'));

        $this->oxid_fs_source = $this->fillShopDir($oxid_fs)->getVfsStreamUrl();
    }

    protected static function getTestData($filepath)
    {
        return __DIR__ . '/testData/' . $filepath;
    }
}

class UnitCommand extends Aggregator
{
    protected $commandName = '';

    /**
     * @inheritDoc
     */
    protected function searchCommands()
    {
        $this->add($this->commandName);
    }

    /**
     * @param string $commandName
     */
    public function setCommandName(string $commandName)
    {
        $this->commandName = $commandName;

        return $this;
    }
}
