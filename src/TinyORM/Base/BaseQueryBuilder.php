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