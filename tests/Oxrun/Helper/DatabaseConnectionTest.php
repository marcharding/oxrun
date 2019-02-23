<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 */


namespace Oxrun\Helper;

use Oxrun\TestCase;

class DatabaseConnectionTest extends TestCase
{

    /**
     * @var DatabaseConnection
     */
    protected $testSubject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $databaseConnection = new DatabaseConnection();
        $databaseConnection
            // Must be right to work correct
            ->setHost($oConfig->getConfigParam('dbHost'))
            ->setPort('3306')
            ->setUser($oConfig->getConfigParam('dbUser'))
            ->setPass($oConfig->getConfigParam('dbPwd'))
            ->setDatabase($oConfig->getConfigParam('dbName'));

        $this->testSubject = $databaseConnection;
    }

    public function testCanParseHostPort()
    {
        $this->testSubject->setHost('127.0.0.1:3336');

        self::assertEquals('127.0.0.1', $this->testSubject->getHost());
        self::assertEquals(3336, $this->testSubject->getPort());
    }

    public function testHasNotConnected()
    {
        $this->testSubject->setDatabase('oxid_unbekannt');

        self::assertFalse($this->testSubject->canConnectToMysql());
        self::assertContains("database 'oxid_unbekannt'", $this->testSubject->getLastErrorMsg());
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
