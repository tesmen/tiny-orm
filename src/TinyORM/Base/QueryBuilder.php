<?php

namespace TinyORM\Base;

class QueryBuilder extends BaseQueryBuilder
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    private $selectFields = ['t.*'];
    private $selectParameters = [];
    private $joins = [];

    /**  @var QueryBuilder[] */
    private $unions = [];
    /**  @var QueryBuilder */
    private $source;

    /**  @var QueryBuilder[] */
    private $with = [];

    private $joinParameters = [];

    private $groupFields = [];
    private $havingConditions = [];
    private $havingParameters = [];

    private $order = [];
    private $orderFields = [];

    private $limit;
    private $offset;

    private $distinctOn;
    private $unionType = 'UNION ALL';

    private $comment;


    /**
     * @param string|QueryBuilder $source - table name or subquery builder
     * @param string $mainAlias
     */
    public function __construct($source, $mainAlias = 't')
    {
        parent::__construct($source, $mainAlias);
        $this->setSource($source);
        $this->selectFields = [$mainAlias . '.*'];
    }

    /**
     * @param string|QueryBuilder $source - table name or subquery builder
     * @return $this
     */
    public function setSource($source)
    {
        if ($source instanceof QueryBuilder) {
            $this->source = $source;
            $this->table = '(' . $source->getQuery() . ')';
        } else {
            $this->table = $source;
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getSourceSql()
    {
        if (isset($this->source)) {
            $result = '(' . $this->source->getQuery() . ')';
        } else {
            $result = $this->table;
        }

        return $result;
    }

    /**
     * Get sql query string
     * @return string
     */
    public function getQuery()
    {
        $table = $this->getSourceSql() . ' ' . $this->mainAlias;

        $select = join(', ', $this->selectFields);

        $distinctOn = $this->distinctOn
            ? 'DISTINCT ON (' . $this->distinctOn . ($this->orderFields
                ? ', ' . join(', ', $this->orderFields)
                : '') . ')'
            : '';

        $join = join(' ', array_unique($this->joins));

        $where = $this->getWhereString();

        $group = $this->groupFields
            ? 'GROUP BY ' . join(', ', $this->groupFields)
            : '';

        $having = $group && $this->havingConditions
            ? 'HAVING ' . join(' AND ', $this->havingConditions)
            : '';

        $order = $this->order
            ? 'ORDER BY ' . join(', ', $this->order)
            : '';

        $limit = $this->limit
            ? 'LIMIT ' . $this->limit
            : '';

        $offset = $this->offset
            ? 'OFFSET ' . $this->offset
            : '';

        $unionAll = $this->unions
            ? $this->unionType . ' ' . implode(' ' . $this->unionType . ' ', $this->getUnionsSQL())
            : '';

        $withSQL = [];
        foreach ($this->with as $alias => $qb) {
            $withSQL[] = $alias . ' AS (' . $qb->getQuery() . ')';
        }
        $with = ($withSQL)
            ? 'WITH ' . implode(', ', $withSQL)
            : '';

        $comment = ($this->comment)
            ? '/* ' . $this->comment . ' */'
            : '';

        $query = "$comment $with SELECT $distinctOn $select FROM $table $join $where $group $having $order $limit $offset";

        $query = $unionAll
            ? "($query) $unionAll"
            : $query;

        return trim($query);
    }

    /**
     * @return array
     */
    private function getUnionsSQL()
    {
        $sql = [];

        foreach ($this->unions as $qb) {
            $sql[] = $qb->getQuery();
        }

        return $sql;
    }

    /**
     * @return array
     */
    public function getBindParameters()
    {
        $unionParameters = [];

        foreach ($this->unions as $qb) {
            $unionParameters = array_merge($unionParameters, $qb->getBindParameters());
        }

        $withParameters = [];

        foreach ($this->with as $qb) {
            $withParameters = array_merge($withParameters, $qb->getBindParameters());
        }

        $sourceParameters = ($this->source)
            ? $this->source->getBindParameters()
            : [];

        return array_merge(
            $withParameters,
            $this->selectParameters,
            $sourceParameters,
            $this->joinParameters,
            parent::getBindParameters(),
            $this->havingParameters,
            $unionParameters
        );
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;

        return $this;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * @param $field
     * @param string $dir
     * @param bool $sanitize
     *
     * @return $this
     */
    public function setOrder($field, $dir = self::ORDER_ASC, $sanitize = true)
    {
        $this->order = [];
        $field && $this->addOrder($field, $dir, $sanitize);

        return $this;
    }

    /**
     * @param $field
     * @param string $dir
     * @param bool $sanitize
     *
     * @return $this
     */
    public function addOrder($field, $dir = self::ORDER_ASC, $sanitize = true)
    {
        if ($sanitize) {
            $field = $this->sanitizeFieldName($field);
            $dir = in_array(strtoupper($dir), [self::ORDER_ASC, self::ORDER_DESC])
                ? $dir
                : self::ORDER_ASC;
        }
        $this->order[] = $field . ' ' . $dir;
        $this->orderFields[] = $field;

        return $this;
    }

    /**
     * Add HAVING condition
     * @param string $condition
     * @param string|array $bind
     *
     * @return $this
     */
    public function addHavingCondition($condition, $bind = [])
    {
        $this->havingConditions[] = $condition;
        $this->havingParameters = array_merge($this->havingParameters, is_array($bind)
            ? $bind
            : [$bind]
        );

        return $this;
    }

    /**
     * @param string $field
     * @param string|array $bind
     * @return $this
     */
    public function addSelectField($field, $bind = [])
    {
        $this->selectFields[] = $field;
        $this->selectParameters = array_merge($this->selectParameters, is_array($bind)
            ? $bind
            : [$bind]
        );

        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function setDistinctOn($field)
    {
        $this->distinctOn = $field
            ? $this->sanitizeFieldName($field)
            : false;

        return $this;
    }

    /**
     * @param array $fields
     * @param string|array $bind
     * @return $this
     */
    public function setSelectFields($fields, $bind = [])
    {
        $this->selectFields = $fields;
        $this->selectParameters = is_array($bind)
            ? $bind
            : [$bind];

        return $this;
    }

    /**
     * @param string $join
     * @param string|array $bind
     * @return $this
     */
    public function addJoin($join, $bind = [])
    {
        if (!in_array($join, $this->joins)) {
            $this->joins[] = $join;
            $this->joinParameters = array_merge($this->joinParameters, is_array($bind)
                ? $bind
                : [$bind]
            );
        }

        return $this;
    }

    /**
     * @param string|bool $field
     * @param bool $sanitize
     * @return $this
     */
    public function setGroup($field, $sanitize = true)
    {
        $this->groupFields = [];

        $field && $this->addGroup($field, $sanitize);

        return $this;
    }

    /**
     * @param string $field
     * @param bool $sanitize
     * @return $this
     */
    public function addGroup($field, $sanitize = true)
    {
        if (!in_array($field, $this->groupFields)) {
            $this->groupFields[] = $sanitize
                ? $this->sanitizeFieldName($field)
                : $field;
        }

        return $this;
    }

    /**
     * @return int|bool
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int|bool
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param QueryBuilder $union
     * @return $this
     */
    public function addUnion(QueryBuilder $union)
    {
        $this->unions[] = $union;

        return $this;
    }

    /**
     * Add WITH clause
     *
     * @param QueryBuilder $with
     * @param string $alias
     * @return $this
     */
    public function addWith(QueryBuilder $with, $alias)
    {
        $this->with[$alias] = $with;

        return $this;
    }

    /**
     * Add WITH clause
     *
     * @param QueryBuilder $with
     * @param string $alias
     * @return $this
     */
    public function addWithRecursive(QueryBuilder $with, $alias)
    {
        $this->with['RECURSIVE ' . $alias] = $with;

        return $this;
    }

    public function setUnionTypeUnionAll()
    {
        $this->unionType = 'UNION ALL';

        return $this;
    }

    public function setUnionTypeUnion()
    {
        $this->unionType = 'UNION';

        return $this;
    }

    /**
     * @return string
     */
    public function buildQueryWithBind()
    {
        $query = $this->getQuery();
        $bind = $this->getBindParameters();

        foreach ($bind as $i) {
            $replacement = is_string($i)
                ? "'{$i}'"
                : "{$i}";
            $query = preg_replace('/\?/', $replacement, $query, 1);
        }

        /** Query pre-formatting for debug */
        $query = preg_replace('~(WITH |AS \(|WHERE |FROM |SELECT )~', "$1\n", $query);
        $query = preg_replace('~(LEFT JOIN|AND)~', "\n$1", $query);
        $query = preg_replace('~BETWEEN (.+?)\\nAND~', "BETWEEN $1 AND", $query);
        $query = preg_replace('~\) SELECT~', ") \nSELECT", $query);
        $query = preg_replace('~\), (.+?) AS \(~', "\n)\n, $1 AS (\n", $query);
        $query = preg_replace('~\(SELECT~', "(\nSELECT", $query);
        $query = preg_replace('~\n\s*?\n~', "\n", $query);

        return $query;
    }
}
