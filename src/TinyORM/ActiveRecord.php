<?php

namespace TinyORM;

use TinyORM\Base\Connection;
use TinyORM\Base\QueryBuilder;
use TinyORM\Base\Smart;

/**
 * Class ActiveRecord
 * @package TinyORM
 *
 * @property int $id
 */
abstract class ActiveRecord extends Smart
{
    const DB_VAL_TRUE = 'TRUE';
    const DB_VAL_FALSE = 'FALSE';

    static $tableName = '';
    static $primaryKey = 'id';

    /**
     * @var Connection $connection
     */
    protected static $connection;

    /**
     * @return Connection
     */
    public static function getConnection()
    {
        return static::$connection;
    }

    /**
     * @param mixed $connection
     */
    public static function setConnection($connection)
    {
        if (static::$connection) {
            return;
        }

        static::$connection = $connection;
    }

    private function isNewRecord()
    {
        return empty($this->aData[static::$primaryKey]);
    }

    private function update()
    {
        $assignments = [];
        $table = static::$tableName;

        foreach ($this->aDataDiff as $fieldName => $fieldValue) {
            if (is_bool($fieldValue)) {
                $dbVal = $fieldValue
                    ? static::DB_VAL_TRUE
                    : static::DB_VAL_FALSE;

                $assignments[] = sprintf("`%s` = %s", $fieldName, $dbVal);
            } else {
                $assignments[] = sprintf("`%s` = '%s'", $fieldName, $fieldValue);
            }
        }

        $assignmentsStr = implode(',', $assignments);

        $sql = "UPDATE {$table} SET {$assignmentsStr} WHERE id={$this->id}";
        $this->aDataDiff = [];

        return static::getConnection()->query($sql);
    }

    private function insert()
    {
        $table = static::$tableName;
        $fields = implode('`,`', array_keys($this->aDataDiff));
        $values = implode("','", array_values($this->aDataDiff));
        $sql = "INSERT INTO {$table} (`{$fields}`) VALUES ('{$values}');";
        $id = static::getConnection()->query($sql);

        if ($id) {
            $primaryName = static::$primaryKey;
            $this->$primaryName = $id;
            $this->flush();
        }

        return $id;
    }

    private function flush()
    {
        $this->aData = $this->aDataDiff;
        $this->aDataDiff = [];
    }

    public function save()
    {
        if ($this->isNewRecord()) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    /**
     * @param array $fields
     * @return array|bool|\PDOStatement
     */
    public static function findByFields(array $fields)
    {
        $data = [];
        $qb = static::createQueryBuilder();

        foreach ($fields as $name => $value) {
            $realVal = is_int($value)
                ? $value
                : "'" . $value . "'";

            $qb->addCondition("t.{$name} = {$realVal}");
        }

        $records = static::getConnection()->query($qb->getQuery());

        if (empty($records)) {
            return [];
        }


        foreach ($records as $record) {
            $data[] = new static($record);
        }

        return $data;
    }

    public static function findById($id)
    {
        $id = intval($id);
        $qb = new QueryBuilder(static::$tableName);
        $qb->addCondition("id = {$id}");
        $records = static::getConnection()->query($qb->getQuery());

        if (empty($records)) {
            return null;
        }

        return new static(reset($records));
    }

    public static function createQueryBuilder()
    {
        return new QueryBuilder(static::$tableName);
    }
}
