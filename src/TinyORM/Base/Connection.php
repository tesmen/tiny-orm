<?php

namespace TinyORM\Base;

use PDO;

class Connection
{
    private $username;
    private $password;
    private $dbName;
    private $serverHost;
    private $attributes;
    private $charset;
    private $errors;

    /**
     * @var PDO $pdo
     */
    private $pdo;

    /**
     * Connection constructor.
     * @param $username
     * @param $password
     * @param $dbName
     * @param $serverHost
     * @param $charset
     */
    public function __construct($username, $password, $dbName, $serverHost = 'localhost', $charset = 'UTF8')
    {
        $this->username = $username;
        $this->password = $password;
        $this->dbName = $dbName;
        $this->serverHost = $serverHost;
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getServerHost()
    {
        return $this->serverHost;
    }

    /**
     * @param string $serverHost
     * @return Connection
     */
    public function setServerHost($serverHost)
    {
        $this->serverHost = $serverHost;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return Connection
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return Connection
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    public function connect()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->serverHost};dbname={$this->dbName}",
                $this->username,
                $this->password,
                $this->attributes
            );

            $this->initConnection();

            return true;
        } catch (\PDOException $e) {
            $this->addError($e->getMessage());

            return false;
        }
    }

    protected function initConnection()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->charset !== null) {
            $this->pdo->exec('SET NAMES ' . $this->pdo->quote($this->charset));
        }
    }

    public function addError($msg)
    {
        $this->errors[] = $msg;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $sql
     * @return array|bool|\PDOStatement
     */
    public function query($sql)
    {
        $st = $this->pdo->query($sql);

        if (strpos(strtolower($sql), 'insert') !== false) {
            return $st->rowCount();
        }

        if (strpos(strtolower($sql), 'update') !== false) {
            return $st->rowCount();
        }

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
