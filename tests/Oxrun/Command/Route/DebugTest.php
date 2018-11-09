<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 23.09.17
 * Time: 22:52
 */

use Oxrun\Application;
use Oxrun\TestCase;
use Oxrun\Command\Route;
use Symfony\Component\Console\Tester\CommandTester;
use \OxidEsales\Eshop\Core\SeoEncoder;

/**
 * Class Debug
 */
class DebugTest extends TestCase
{
    const commandName = 'route:debug';
    /**
     * @var Route\DebugCommand|CommandTester
     */
    protected $commandTester;

    protected function setUp()
    {
        $app = new Application();
        $app->add(new Route\DebugCommand());

        $command = $app->find(self::commandName);

        $this->commandTester = new CommandTester($command);
    }

    public function testCompleteUrl()
    {
        $this->commandTester->execute(
            array(
                'url' => 'http://localhost/warenkorb/',
                'command' => self::commandName,
            )
        );

        $this->assertRegExp('~\|\s+Controller\s+\|\s+basket\s+\|~', $this->commandTester->getDisplay());
    }

    public function testHalfBrockenUrl()
    {
        $this->commandTester->execute(
            array(
                'url' => 'http://localhost/warenkorb',
                'command' => self::commandName,
            )
        );

        $this->assertRegExp('~\|\s+Controller\s+\|\s+basket\s+\|~', $this->commandTester->getDisplay());
    }

    public function testOnlyPath()
    {
        $this->commandTester->execute(
            array(
                'url' => 'warenkorb/',
                'command' => self::commandName,
            )
        );

        $this->assertRegExp('~\|\s+Controller\s+\|\s+basket\s+\|~', $this->commandTester->getDisplay());
    }

    public function testHalfOnlyPath()
    {
        $this->commandTester->execute(
            array(
                'url' => 'warenkorb',
                'command' => self::commandName,
            )
        );

        $this->assertRegExp('~\|\s+Controller\s+\|\s+basket\s+\|~', $this->commandTester->getDisplay());
    }

    public function testGiveMeClassPath()
    {
        $this->commandTester->execute(
            array(
                'url' => 'warenkorb',
                'command' => self::commandName,
            )
        );

        $this->assertContains('BasketController.php', $this->commandTester->getDisplay());
    }

    public function testGiveFunctionLineNumber()
    {
        /** @var SeoEncoder $SeoEncoder */
        $SeoEncoder = oxNew(SeoEncoder::class);
        $SeoEncoder->getDynamicUrl('index.php?cl=news&amp;fnc=render', 'newspage/',  0);

        $this->commandTester->execute(
            array(
                'url' => 'NewsPage/',
                'command' => self::commandName,
            )
        );

        $this->assertRegExp('~NewsController.php:\d+~', $this->commandTester->getDisplay());
    }

    public function testClassDontExists()
    {
        /** @var \SeoEncoder $SeoEncoder */
        $SeoEncoder = oxNew(SeoEncoder::class);
        $SeoEncoder->getDynamicUrl('index.php?cl=classdontexists', 'class/dont/exists/',  0);

        $this->commandTester->execute(
            array(
                'url' => 'Class/Dont/Exists/',
                'command' => self::commandName,
            )
        );

        $this->assertContains('Class classdontexists does not exist', $this->commandTester->getDisplay());
    }

    public function testMethodInClassDontExists()
    {
        /** @var SeoEncoder $SeoEncoder */
        $SeoEncoder = oxNew(SeoEncoder::class);
        $SeoEncoder->getDynamicUrl('index.php?cl=news&amp;fnc=nameXYX', 'method/in/class/dont/exists/',  0);

        $this->commandTester->execute(
            array(
                'url' => 'Method/In/Class/Dont/Exists/',
                'command' => self::commandName,
            )
        );

        $this->assertContains('Method nameXYX does not exist', $this->commandTester->getDisplay());
    }

    /**
     * Reset seo db
     */
    public static function tearDownAfterClass()
    {
        $db = OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        $seoURls[] = $db->quote('newspage/');
        $seoURls[] = $db->quote('class/dont/exists/');
        $seoURls[] = $db->quote('method/in/class/dont/exists/');
        $seoURls = implode(", ", $seoURls);

        $db->execute("DELETE FROM oxseo WHERE OXSEOURL IN ($seoURls)");
    }
}