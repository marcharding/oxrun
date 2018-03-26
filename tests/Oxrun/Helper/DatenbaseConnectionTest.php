<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 */


namespace Oxrun\Helper;


class DatenbaseConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected static $config;

    /**
     * @var DatenbaseConnection
     */
    protected $testSubject;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        self::$config = new \OxidFileConfig();
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $port = isset(self::$config->dbPort) ? self::$config->dbPort : 3306;

        $datenbaseConnection = new DatenbaseConnection();
        $datenbaseConnection
            // Must be right to work correct
            ->setHost( self::$config->dbHost)
            ->setPort($port)
            ->setUser(self::$config->dbUser)
            ->setPass(self::$config->dbPwd)
            ->setDatabase(self::$config->dbName);

        $this->testSubject = $datenbaseConnection;
    }

    public function testCanParseHostPort()
    {
        $this->testSubject->setHost('127.0.0.1:3336');

        self::assertEquals('127.0.0.1', $this->testSubject->getHost());
        self::assertEquals(3336,        $this->testSubject->getPort());
    }

    public function testHasNotConnected()
    {
        $this->testSubject->setDatabase('oxid_unbekannt');

        self::assertFalse($this->testSubject->canConnectToMysql());
        self::assertContains("SQLSTATE[HY000]", $this->testSubject->getLastErrorMsg());
    }

    /**
     * @dataProvider getEmptyConfigs
     */
    public function testHasEmptyConfig($func, $value)
    {
        $this->testSubject->$func($value);

        self::assertFalse($this->testSubject->canConnectToMysql());
        self::assertContains("are empty", $this->testSubject->getLastErrorMsg());
    }

    public function getEmptyConfigs()
    {
        return [
            'No Host Config' => ['func'=>'setHost', 'value' => ''],
            'No Port Config' => ['func'=>'setPort', 'value' => 0],
            'No User Config' => ['func'=>'setUser', 'value' => ''],
        ];
    }

    public function testCanConnected()
    {
        self::assertTrue($this->testSubject->canConnectToMysql());
    }

    public function testExecuteSelect()
    {
        $result = $this->testSubject->execute('SELECT OXID FROM oxconfig');
        self::assertTrue($result);
    }

    public function testExecuteSelectWithParams()
    {
        $SQL = 'SELECT OXID FROM oxconfig WHERE OXVARNAME LIKE ?';

        $result = $this->testSubject->execute($SQL, ['%modules%']);
        self::assertTrue($result);
    }
}
