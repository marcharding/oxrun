<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 */


namespace Oxrun\Helper;

/**
 * Class DatabaseConnection
 *
 * Connect direct to Database
 *
 * @package Oxrun\Helper
 */
class DatabaseConnection
{

    /**
     * @var string
     */
    protected $lastErrorMsg = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port = 3306;

    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $pass = '';

    /**
     * @var string
     */
    protected $database = '';

    /**
     * @var \PDO
     */
    protected $PDO = null;

    /**
     * @param string $host
     * @return DatabaseConnection
     */
    public function setHost($host)
    {
        $this->host = $host;

        if (preg_match('/^([^:]+):(\d+)$/', $host, $matchs)) {
            $this->host = $matchs[1];
            $this->setPort($matchs[2]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param int $port
     * @return DatabaseConnection
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $user
     * @return DatabaseConnection
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $pass
     * @return DatabaseConnection
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @param string $database
     * @return DatabaseConnection
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * Checks if can connect to a mysql DB
     *
     * @return bool
     */
    public function canConnectToMysql()
    {
        if ($this->PDO === null) {
            try {
                $this->inspectAccessData();
            } catch (\LogicException $e) {
                $this->lastErrorMsg = $e->getMessage();
                return false;
            }

            $dsn = sprintf('mysql:dbname=%s;host=%s;port=%s', $this->database, $this->host, $this->port);

            try {
                $this->PDO = new \PDO($dsn, $this->user, $this->pass);
            } catch (\Exception $e) {
                $this->lastErrorMsg = $e->getMessage();
                return false;
            }
        }

        return is_object($this->PDO);
    }

    /**
     * Execute a SQL Command
     *
     * @param $SQL
     *
     * @return bool
     */
    public function execute($SQL, $params = null)
    {
        if ($this->canConnectToMysql()) {
            $PDOStatement = $this->PDO->prepare($SQL);
            $result = $PDOStatement->execute($params);
            $PDOStatement->closeCursor();
            return $result;
        }

        return false;
    }

    protected function inspectAccessData()
    {
        if ($this->host == '' || $this->port == false || $this->user == '') {
            throw new \LogicException('The config param DB::host, DB::port or DB::user are empty');
        }
    }

    /**
     * @return mixed
     */
    public function getLastErrorMsg()
    {
        return $this->lastErrorMsg;
    }
}