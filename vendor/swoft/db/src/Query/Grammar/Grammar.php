<?php declare(strict_types=1);


namespace Swoft\Db\Query\Grammar;

use ReflectionException;
use RuntimeException;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Stdlib\Collection;
use Swoft\Db\Grammar as BaseGrammar;
use Swoft\Db\Query\Builder;
use Swoft\Db\Query\Expression;
use Swoft\Db\Query\JoinClause;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Stdlib\Helper\Str;
use function end;
use function reset;

/**
 * Class Grammar
 *
 * @since 2.0
 */
class Grammar extends BaseGrammar
{
    /**
     * The grammar specific operators.
     *
     * @var array
     */
    protected $operators = [];

    /**
     * The components that make up a select clause.
     *
     * @var array
     */
    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions',
        'lock',
    ];

    /**
     * Compile a select query into SQL.
     *
     * @param Builder $query
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileSelect(Builder $query)
    {
        if ($query->unions && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }

        // If the query does not have any columns set, we'll set the columns to the
        // * character to just get all of the columns from the database. Then we
        // can build the query and concatenate all the pieces together as one.
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the SQL.
        $sql = trim($this->concatenate(
            $this->compileComponents($query))
        );

        $query->columns = $original;

        return $sql;
    }

    /**
     * Compile the components necessary for a select clause.
     *
     * @param Builder $query
     *
     * @return array
     */
    protected function compileComponents(Builder $query)
    {
        $sql = [];

        foreach ($this->selectComponents as $component) {
            // To compile the query, we'll spin through each component of the query and
            // see if that component exists. If it does we'll just call the compiler
            // function for the component which is responsible for making the SQL.
            if (isset($query->$component) && !is_null($query->$component)) {
                $method = 'compile' . ucfirst($component);

                $sql[$component] = $this->$method($query, $query->$component);
            }
        }

        return $sql;
    }

    /**
     * Compile an aggregated select clause.
     *
     * @param Builder $query
     * @param array   $aggregate
     *
     * @return string
     */
    protected function compileAggregate(Builder $query, $aggregate)
    {
        $column = $this->columnize($aggregate['columns']);

        // If the query has a "distinct" constraint and we're not asking for all columns
        // we need to prepend "distinct" onto the column name so that the query takes
        // it into account when it performs the aggregating operations on the data.
        if ($query->distinct && $column !== '*') {
            $column = 'distinct ' . $column;
        }

        return 'select ' . $aggregate['function'] . '(' . $column . ') as aggregate';
    }

    /**
     * Compile the "select *" portion of the query.
     *
     * @param Builder $query
     * @param array   $columns
     *
     * @return string
     */
    protected function compileColumns(Builder $query, $columns): string
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax that is best handled by that function to keep things neat.
        if (!is_null($query->aggregate)) {
            return '';
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select . $this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     *
     * @param Builder $query
     * @param string  $table
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function compileFrom(Builder $query, $table)
    {
        return 'from ' . $this->wrapTable($table);
    }

    /**
     * Compile the "join" portions of the query.
     *
     * @param Builder $query
     * @param array   $joins
     *
     * @return string
     */
    protected function compileJoins(Builder $query, $joins)
    {
        return Collection::make($joins)->map(function ($join) use ($query) {
            $table = $this->wrapTable($join->table);

            $nestedJoins = is_null($join->joins) ? '' : ' ' . $this->compileJoins($query, $join->joins);

            $tableAndNestedJoins = is_null($join->joins) ? $table : '(' . $table . $nestedJoins . ')';

            return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
        })->implode(' ');
    }

    /**
     * Compile the "where" portions of the query.
     *
     * @param Builder $query
     *
     * @return string
     */
    protected function compileWheres(Builder $query)
    {
        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses SQL. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        if (is_null($query->wheres)) {
            return '';
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($query, $sql);
        }

        return '';
    }

    /**
     * Get an array of all the where clauses for the query.
     *
     * @param Builder $query
     *
     * @return array
     */
    protected function compileWheresToArray($query)
    {
        return Collection::make($query->wheres)->map(function ($where) use ($query) {
            return $where['boolean'] . ' ' . $this->{"where{$where['type']}"}($query, $where);
        })->all();
    }

    /**
     * Format the where clause statements into one string.
     *
     * @param Builder $query
     * @param array   $sql
     *
     * @return string
     */
    protected function concatenateWhereClauses($query, $sql)
    {
        $conjunction = $query instanceof JoinClause ? 'on' : 'where';

        return $conjunction . ' ' . $this->removeLeadingBoolean(implode(' ', $sql));
    }

    /**
     * Compile a raw where clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     */
    protected function whereRaw(Builder $query, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereIn(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereNotIn(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where not in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereNotInRaw(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(', ', $where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a where in sub-select clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereInSub(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' in (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where not in sub-select clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereNotInSub(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' not in (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a "where in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereInRaw(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where null" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is null';
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereNotNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    /**
     * Compile a "between" where clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereBetween(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(reset($where['values']));

        $max = $this->parameter(end($where['values']));

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile a "where date" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereDate(Builder $query, $where)
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereTime(Builder $query, $where)
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    /**
     * Compile a "where day" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereDay(Builder $query, $where)
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereMonth(Builder $query, $where)
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereYear(Builder $query, $where)
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile a date based where clause.
     *
     * @param string  $type
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $type . '(' . $this->wrap($where['column']) . ') ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a where clause comparing two columns..
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereColumn(Builder $query, $where)
    {
        return $this->wrap($where['first']) . ' ' . $where['operator'] . ' ' . $this->wrap($where['second']);
    }

    /**
     * Compile a nested where clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     */
    protected function whereNested(Builder $query, $where)
    {
        // Here we will calculate what portion of the string we need to remove. If this
        // is a join clause query, we need to remove the "on" portion of the SQL and
        // if it is a normal query we need to take the leading "where" of queries.
        $offset = $query instanceof JoinClause ? 3 : 6;

        return '(' . substr($this->compileWheres($where['query']), $offset) . ')';
    }

    /**
     * Compile a where condition with a sub-select.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereSub(Builder $query, $where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . " ($select)";
    }

    /**
     * Compile a where exists clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereExists(Builder $query, $where)
    {
        return 'exists (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where exists clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function whereNotExists(Builder $query, $where)
    {
        return 'not exists (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where row values condition.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     */
    protected function whereRowValues(Builder $query, $where)
    {
        $columns = $this->columnize($where['columns']);

        $values = $this->parameterize($where['values']);

        return '(' . $columns . ') ' . $where['operator'] . ' (' . $values . ')';
    }

    /**
     * Compile a "where JSON contains" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     */
    protected function whereJsonContains(Builder $query, $where)
    {
        $not = $where['not'] ? 'not ' : '';

        return $not . $this->compileJsonContains(
                $where['column'], $this->parameter($where['value'])
            );
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param string $column
     * @param string $value
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function compileJsonContains($column, $value)
    {
        throw new RuntimeException('This database engine does not support JSON contains operations.');
    }

    /**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param mixed $binding
     *
     * @return string
     */
    public function prepareBindingForJsonContains($binding)
    {
        return json_encode($binding);
    }

    /**
     * Compile a "where JSON length" clause.
     *
     * @param Builder $query
     * @param array   $where
     *
     * @return string
     */
    protected function whereJsonLength(Builder $query, $where)
    {
        return $this->compileJsonLength(
            $where['column'], $where['operator'], $this->parameter($where['value'])
        );
    }

    /**
     * Compile a "JSON length" statement into SQL.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function compileJsonLength($column, $operator, $value)
    {
        throw new RuntimeException('This database engine does not support JSON length operations.');
    }

    /**
     * Compile the "group by" portions of the query.
     *
     * @param Builder $query
     * @param array   $groups
     *
     * @return string
     */
    protected function compileGroups(Builder $query, $groups)
    {
        return 'group by ' . $this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     *
     * @param Builder $query
     * @param array   $havings
     *
     * @return string
     */
    protected function compileHavings(Builder $query, $havings)
    {
        $sql = implode(' ', array_map([$this, 'compileHaving'], $havings));

        return 'having ' . $this->removeLeadingBoolean($sql);
    }

    /**
     * Compile a single having clause.
     *
     * @param array $having
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function compileHaving(array $having)
    {
        // If the having clause is "raw", we can just return the clause straight away
        // without doing any more processing on it. Otherwise, we will compile the
        // clause into SQL based on the components that make it up from builder.
        if ($having['type'] === 'Raw') {
            return $having['boolean'] . ' ' . $having['sql'];
        } elseif ($having['type'] === 'between') {
            return $this->compileHavingBetween($having);
        }

        return $this->compileBasicHaving($having);
    }

    /**
     * Compile a basic having clause.
     *
     * @param array $having
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function compileBasicHaving($having)
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'] . ' ' . $column . ' ' . $having['operator'] . ' ' . $parameter;
    }

    /**
     * Compile a "between" having clause.
     *
     * @param array $having
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function compileHavingBetween($having)
    {
        $between = $having['not'] ? 'not between' : 'between';

        $column = $this->wrap($having['column']);

        $min = $this->parameter(reset($having['values']));

        $max = $this->parameter(end($having['values']));

        return $having['boolean'] . ' ' . $column . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile the "order by" portions of the query.
     *
     * @param Builder $query
     * @param array   $orders
     *
     * @return string
     */
    protected function compileOrders(Builder $query, $orders)
    {
        if (!empty($orders)) {
            return 'order by ' . implode(', ', $this->compileOrdersToArray($query, $orders));
        }

        return '';
    }

    /**
     * Compile the query orders to an array.
     *
     * @param Builder $query
     * @param array   $orders
     *
     * @return array
     */
    protected function compileOrdersToArray(Builder $query, $orders)
    {
        return array_map(function ($order) {
            return !isset($order['sql'])
                ? $this->wrap($order['column']) . ' ' . $order['direction']
                : $order['sql'];
        }, $orders);
    }

    /**
     * Compile the random statement into SQL.
     *
     * @param string $seed
     *
     * @return string
     */
    public function compileRandom($seed)
    {
        return 'RANDOM()';
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param Builder $query
     * @param int     $limit
     *
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return 'limit ' . (int)$limit;
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param Builder $query
     * @param int     $offset
     *
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return 'offset ' . (int)$offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     *
     * @param Builder $query
     *
     * @return string
     */
    protected function compileUnions(Builder $query)
    {
        $sql = '';

        foreach ($query->unions as $union) {
            $sql .= $this->compileUnion($union);
        }

        if (!empty($query->unionOrders)) {
            $sql .= ' ' . $this->compileOrders($query, $query->unionOrders);
        }

        if (isset($query->unionLimit)) {
            $sql .= ' ' . $this->compileLimit($query, $query->unionLimit);
        }

        if (isset($query->unionOffset)) {
            $sql .= ' ' . $this->compileOffset($query, $query->unionOffset);
        }

        return ltrim($sql);
    }

    /**
     * Compile a single union statement.
     *
     * @param array $union
     *
     * @return string
     */
    protected function compileUnion(array $union)
    {
        $conjunction = $union['all'] ? ' union all ' : ' union ';

        return $conjunction . $union['query']->toSql();
    }

    /**
     * Compile a union aggregate query into SQL.
     *
     * @param Builder $query
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function compileUnionAggregate(Builder $query)
    {
        $sql = $this->compileAggregate($query, $query->aggregate);

        $query->aggregate = null;

        return $sql . ' from (' . $this->compileSelect($query) . ') as ' . $this->wrapTable('temp_table');
    }

    /**
     * Compile an exists statement into SQL.
     *
     * @param Builder $query
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileExists(Builder $query)
    {
        $select = $this->compileSelect($query);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param Builder $query
     * @param array   $values
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileInsert(Builder $query, array $values)
    {
        // Essentially we will force every insert to be treated as a batch insert which
        // simply makes creating the SQL easier for us since we can utilize the same
        // basic routine regardless of an amount of records given to us to insert.
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        // We need to build a list of parameter place-holders of values that are bound
        // to the query. Each insert should have the exact same amount of parameter
        // bindings so we will loop through the record and parameterize them all.
        $parameters = Collection::make($values)->map(function ($record) {
            return '(' . $this->parameterize($record) . ')';
        })->implode(', ');

        return "insert into $table ($columns) values $parameters";
    }

    /**
     * Compile batch update Sql
     *
     * @param Builder $query
     * @param array   $values
     * @param string  $primary
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileBatchUpdateByIds(Builder $query, array $values, string $primary)
    {
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        // Take the first value as columns
        $columns = array_keys(current($values));

        $setStr = '';
        foreach ($columns as $column) {
            if ($column === $primary) {
                continue;
            }

            $setStr .= " `$column` = case `$primary` ";
            foreach ($values as $row) {
                $value    = $row[$column];
                $rowValue = is_string($value) ? "'$value'" : $value;

                $setStr .= " when '$row[$primary]' then $rowValue ";
            }
            $setStr .= ' end,';
        }
        // Remove the last character
        $setStr = substr($setStr, 0, -1);

        $ids    = array_column($values, $primary);
        $idsStr = implode(',', $ids);

        return "update $table set $setStr where $primary in ($idsStr)";
    }

    /**
     * Compile an insert and get ID statement into SQL.
     *
     * @param Builder $query
     * @param array   $values
     * @param string  $sequence
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileInsertGetId(Builder $query, $values, $sequence)
    {
        return $this->compileInsert($query, $values);
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     *
     * @param Builder $query
     * @param array   $columns
     * @param string  $sql
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileInsertUsing(Builder $query, array $columns, string $sql)
    {
        return "insert into {$this->wrapTable($query->from)} ({$this->columnize($columns)}) $sql";
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param Builder $query
     * @param array   $values
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);

        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.
        $columns = Collection::make($values)->map(function ($value, $key) {
            return $this->wrap($key) . ' = ' . $this->parameter($value);
        })->implode(', ');

        // If the query has any "join" clauses, we will setup the joins on the builder
        // and compile them so we can attach them to this update, as update queries
        // can get join statements to attach to other tables when they're needed.
        $joins = '';

        if (isset($query->joins)) {
            $joins = ' ' . $this->compileJoins($query, $query->joins);
        }

        // Of course, update queries may also be constrained by where clauses so we'll
        // need to compile the where clauses and attach it to the query so only the
        // intended records are updated by the SQL statements we generate to run.
        $wheres = $this->compileWheres($query);

        return trim("update {$table}{$joins} set $columns $wheres");
    }

    /**
     * Prepare the bindings for an update statement.
     *
     * @param array $bindings
     * @param array $values
     *
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $cleanBindings = Arr::except($bindings, ['join', 'select']);

        return array_values(
            array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile a delete statement into SQL.
     *
     * @param Builder $query
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileDelete(Builder $query)
    {
        $wheres = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return trim("delete from {$this->wrapTable($query->from)} $wheres");
    }

    /**
     * Prepare the bindings for a delete statement.
     *
     * @param array $bindings
     *
     * @return array
     */
    public function prepareBindingsForDelete(array $bindings)
    {
        return Arr::flatten($bindings);
    }

    /**
     * Compile a truncate table statement into SQL.
     *
     * @param Builder $query
     *
     * @return array
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compileTruncate(Builder $query)
    {
        return ['truncate ' . $this->wrapTable($query->from) => []];
    }

    /**
     * Compile the lock into SQL.
     *
     * @param Builder     $query
     * @param bool|string $value
     *
     * @return string
     */
    protected function compileLock(Builder $query, $value)
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Determine if the grammar supports savepoints.
     *
     * @return bool
     */
    public function supportsSavepoints()
    {
        return true;
    }

    /**
     * Compile the SQL statement to define a savepoint.
     *
     * @param string $name
     *
     * @return string
     */
    public function compileSavepoint($name)
    {
        return 'SAVEPOINT ' . $name;
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
     *
     * @param string $name
     *
     * @return string
     */
    public function compileSavepointRollBack($name)
    {
        return 'ROLLBACK TO SAVEPOINT ' . $name;
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param Expression|string $value
     * @param bool              $prefixAlias
     *
     * @return string
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function wrap($value, $prefixAlias = false)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on its
        // own, and then join these both back together using the "as" connector.
        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }

        // If the given value is a JSON selector we will wrap it differently than a
        // traditional value. We will need to split this path and wrap each part
        // wrapped, etc. Otherwise, we will simply wrap the value as a string.
        if ($this->isJsonSelector($value)) {
            return $this->wrapJsonSelector($value);
        }

        return $this->wrapSegments(explode('.', $value));
    }

    /**
     * Wrap the given JSON selector.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrapJsonSelector($value)
    {
        throw new RuntimeException('This database engine does not support JSON operations.');
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     *
     * @param string $column
     *
     * @return array
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function wrapJsonFieldAndPath($column)
    {
        $parts = explode('->', $column, 2);

        $field = $this->wrap($parts[0]);

        $path = count($parts) > 1 ? ', ' . $this->wrapJsonPath($parts[1]) : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrapJsonPath($value)
    {
        return '\'$."' . str_replace('->', '"."', $value) . '"\'';
    }

    /**
     * Determine if the given string is a JSON selector.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function isJsonSelector($value)
    {
        return Str::contains($value, '->');
    }

    /**
     * Concatenate an array of segments, removing empties.
     *
     * @param array $segments
     *
     * @return string
     */
    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string)$value !== '';
        }));
    }

    /**
     * Remove the leading boolean from a statement.
     *
     * @param string $value
     *
     * @return string
     */
    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    /**
     * Get the grammar specific operators.
     *
     * @return array
     */
    public function getOperators()
    {
        return $this->operators;
    }
}