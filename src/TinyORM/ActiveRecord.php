<?php

namespace TinyORM;

use TinyORM\Base\Connection;
use TinyORM\Base\Smart;

class ActiveRecord extends Smart
{
    static $tableName = '';

    /**
     * @var Connection $connection
     */
    private static $connection;

    /**
     * @return Connection
     */
    public static function getConnection()
    {
        return self::$connection;
    }

    /**
     * @param mixed $connection
     */
    public static function setConnection($connection)
    {
        if (self::$connection) {
            return;
        }

        self::$connection = $connection;
    }

    private function isRecordNew()
    {
        return empty($this->aData['id']);
    }

    private function insert()
    {
        $table = static::$tableName;
        $fields = implode('`,`', array_keys($this->aDataDiff));
        $values = implode("','", array_values($this->aDataDiff));
        $sql = "INSERT INTO {$table} (`{$fields}`) VALUES ('{$values}')";

        $this->aDataDiff = [];

        return static::getConnection()->query($sql);
    }

    public function save()
    {
        if ($this->isRecordNew()) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    public static function findById($id)
    {
        $table = static::$tableName;
        $sql = "SELECT * FROM {$table} WHERE id = {intval($id)} LIMIT 1";
        $records = static::getConnection()->query($sql);

        if (empty($records)) {
            return null;
        }

        return new static(reset($records));
    }
}
