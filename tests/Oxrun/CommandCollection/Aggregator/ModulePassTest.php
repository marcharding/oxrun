<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 07.03.19
 * Time: 13:52
 */

namespace Oxrun\CommandCollection\Aggregator;

use Oxrun\CommandCollection\CacheCheck;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class ModulePassTest
 * @package Oxrun\CommandCollection\Aggregator
 * @group active
 */
class ModulePassTest extends \Oxrun\TestCase
{
    /**
     * @var Definition|ObjectProphecy|MethodProphecy
     */
    protected $definition;

    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    public function testLoadModuleCommand()
    {
        //Arrange
        $customCommand = new ModulePass();
        $customCommand->setShopDir($this->mockCommand());
        $customCommand->setConsoleOutput(new NullOutput());

        //Assert
        $this->definition->addMethodCall(Argument::any(), Argument::any())->shouldBeCalled();

        //Act
        $customCommand->process($this->containerBuilder);

        //Assert
        $this->assertCount(3, CacheCheck::getResource());
    }

    public function testLoadModuleCommandWithSyntaxError()
    {
        //Arrange
        $customCommand = new ModulePass();
        $customCommand->setShopDir($this->mockCommandHasSyntaxError());
        $bufferedOutput = new BufferedOutput();
        $customCommand->setConsoleOutput($bufferedOutput);

        //Act
        $customCommand->process($this->containerBuilder);

        //Assert
        $this->assertEquals('Can not add Command vfs://installation_root_path/source/modules/tm/planet/Commands/ModuleSyntaxCommand.php:syntax error, unexpected end of file'. PHP_EOL, $bufferedOutput->fetch());
    }

    public function testCanNotLoadAModuleCommandWithOtherName()
    {
        //Arrange
        $customCommand = new ModulePass();
        $customCommand->setShopDir($this->mockCommandWithWrongFilename());
        $bufferedOutput = new BufferedOutput();
        $customCommand->setConsoleOutput($bufferedOutput);

        //Act
        $customCommand->process($this->containerBuilder);

        //Assert
        $this->assertCount(1, CacheCheck::getResource());
        $this->assertEquals(
            ''.
            'Class \'WrongNameCommand\' was not inside: vfs://installation_root_path/source/modules/tm/planet/Commands/WrongNameCommand.php'. PHP_EOL .
            'NameCommand is not a compatible command'. PHP_EOL,

            $bufferedOutput->fetch());

    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->definition = $this->prophesize(Definition::class);
        $this->definition->hasTag(Argument::any())->willReturn(false);

        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setDefinition('command_container', $this->definition->reveal());
    }

    protected function tearDown()
    {
        CacheCheck::clean();
        parent::tearDown();
    }

    protected function mockCommand() {
        $oxid_fs['source']['modules']['tm']['planet']['Commands']['ModuleCommand.php'] = file_get_contents(self::getTestData('ModuleCommand.php'));
        $oxid_fs['source']['modules']['tm']['planet']['Command']['modulesuncommand.php'] = file_get_contents(self::getTestData('ModuleSunCommand.php'));
        $oxid_fs['source']['modules']['tm']['planet']['commands']['ModuleSunshineCommand.php'] = file_get_contents(self::getTestData('ModuleSunshineCommand.php'));
        $oxid_fs['source']['modules']['tm']['planet']['Commands']['ModuleSunshine.php'] = '<?php  throw new Exception("Do not load");';

        return $this->fillShopDir($oxid_fs)->getVfsStreamUrl();
    }

    protected function mockCommandHasSyntaxError() {
        $oxid_fs['source']['modules']['tm']['planet']['Commands']['ModuleSyntaxCommand.php'] = '<?php  $a; $b = functionNone()';

        return $this->fillShopDir($oxid_fs)->getVfsStreamUrl();
    }

    protected function mockCommandWithWrongFilename() {
        $oxid_fs['source']['modules']['tm']['planet']['Commands']['WrongNameCommand.php'] = '<?php class NameCommand {}';

        return $this->fillShopDir($oxid_fs)->getVfsStreamUrl();
    }

    protected static function getTestData($filepath)
    {
        return __DIR__ . '/../testData/commands/' . $filepath;
    }
}
