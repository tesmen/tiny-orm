<?php

namespace TinyORM\Base;

abstract class BaseQueryBuilder
{
    protected $table;
    protected $mainAlias;

    private $conditions = [];
    private $whereClauseParameters = [];

    /**
     * @param string $table
     * @param string $mainAlias
     */
    public function __construct($table = null, $mainAlias = 't')
    {
        $this->table = $table;
        $this->mainAlias = $mainAlias;
    }

    /**
     * Get sql query string
     * @return string
     */
    public abstract function getQuery();

    /**
     * @return array
     */
    public function getBindParameters()
    {
        return $this->whereClauseParameters;
    }

    /**
     * Add WHERE condition
     * @param string $condition
     * @param string|array $bind
     *
     * @return $this
     */
    public function addCondition($condition, $bind = [])
    {
        $conditionArr = explode('?', $condition);

        $bind = is_array($bind)
            ? $bind
            : [$bind];

        $condition = '';

        foreach ($bind as $key => $value) {
            if (is_array($value) && ($cnt = count($value)) > 0) {
                $condition .= $conditionArr[$key] . implode(',', array_pad([], $cnt, '?'));
                $this->whereClauseParameters = array_merge($this->whereClauseParameters, $value);
            } else {
                $condition .= $conditionArr[$key] . '?';
                $this->whereClauseParameters[] = is_array($value)
                    ? 0
                    : $value;
            }

            unset($conditionArr[$key]);
        }

        $this->conditions[] = $condition . (empty($conditionArr)
                ? ''
                : array_shift($conditionArr));

        return $this;
    }

    public function __toString()
    {
        return $this->getQuery();
    }

    /**
     * @return string
     */
    protected function getWhereString()
    {
        return $this->conditions
            ? 'WHERE ' . join(' AND ', $this->conditions)
            : '';
    }

    /**
     * Converts alias.field to "alias"."field" and replace '"' by '""'.
     * @param string $field
     * @return string
     */
    protected function sanitizeFieldName($field)
    {
        return join(
            '.', array_map(
                function ($s) {
                    return '"' . str_replace('"', '""', $s) . '"';
                }, explode('.', $field)
            )
        );
    }
}