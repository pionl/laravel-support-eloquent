<?php

namespace Pion\Support\Eloquent\Traits;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;
use DB;
use Schema;
use Illuminate\Database\Query\Builder;

/**
 * Class RelationJoinTrait.
 *
 * Trait to create model join for scope with detection of model in the attributes.
 *
 * Pre-fills the relations array.
 *
 * @property array $relationAliases A list of relations that has different method name than the table. Can be defined
 *                                  in model like this:
 *                                  protected $relationAliases = [
 *                                  "activity_type" => "type"
 *                                  ];
 *
 * @method setRelation($table, $instance)
 */
trait RelationJoinTrait
{
    /**
     * This determines the foreign key relations automatically to prevent the need to figure out the columns.
     *
     * Based on http://laravel-tricks.com/tricks/automatic-join-on-eloquent-models-with-relations-setup
     *
     * @param Builder $query
     * @param                                    $relationName
     * @param string                             $operatorOrColumns ON condition operator
     * @param string                             $type              join type (left, right, '', etc)
     * @param array                              $columns           if you will not pass columns, it will retrieve the
     *                                                              column listing. If you pass null it will not get
     *                                                              any data from the model. all columns *
     * @param callable|null                      $extendJoin        Closure that receives JoinClause as first parameter.
     *
     * @return Builder
     *
     * @see http://laravel-tricks.com/tricks/automatic-join-on-eloquent-models-with-relations-setup
     */
    public function scopeModelJoin($query, $relationName, $operatorOrColumns = '=', $type = 'left', $columns = [],
                                   callable $extendJoin = null)
    {
        /** @var Relation $relation */
        $relation = $this->$relationName();
        $table = $relation->getRelated()->getTable();

        // use different relation column for HasOneOrMany relation
        if ($relation instanceof HasOneOrMany) {
            $one = $relation->getQualifiedParentKeyName();
            $two = $relation->getQualifiedForeignKeyName();
        } else {
            $one = $relation->getRelated()->getQualifiedKeyName();
            $two = "{$relation->getParent()->getTable()}.{$relation->getForeignKeyName()}";
        }

        return $this->scopeJoinWithSelect(
            $query,
            $table,
            $one,
            $operatorOrColumns,
            $two,
            $type,
            $columns,
            // Relations can contain custom where conditions - migrate the conditions to join clause
            function (JoinClause $joinClause) use ($extendJoin, $relation, $relationName, $table) {
                if (is_callable($extendJoin)) {
                    $extendJoin($joinClause);
                }

                // Try to handle custom where conditions used on relation
                $query = $relation->getQuery()->getQuery();

                // Relations builds where condition for NotNull
                if ($relation instanceof HasOneOrMany) {
                    // Has relations builds Null/NotNull where conditions, we need to remove them
                    $whereConditionsWithoutRelationConditions = array_slice($query->wheres, 2);
                } else {
                    $whereConditionsWithoutRelationConditions = array_slice($query->wheres, 1);
                }

                // Merge where conditions with bindings
                if (false === empty($whereConditionsWithoutRelationConditions)) {
                    $wheres = [];
                    $bindings = [];
                    // Append table alias to column
                    foreach ($whereConditionsWithoutRelationConditions as $condition) {
                        // Remove the table - we are using alias
                        $columnWithoutTableName = str_replace($table.'.', '', $condition['column']);

                        // Replace the where condition with alias
                        $condition['column'] = DB::raw("`{$relationName}`.`{$columnWithoutTableName}`");
                        $wheres[] = $condition;
                        if (array_key_exists('value', $condition) === true) {
                            $bindings[] = $condition['value'];   
                        }
                    }

                    // Merge the where condition
                    $joinClause->mergeWheres($wheres, $bindings);
                }
            },
            // Relation name is used as table alias
            $relationName
        );
    }

    /**
     * Replaces the table in name in column with alias.
     *
     * @param string $column
     * @param string $table
     * @param string $alias
     *
     * @return string
     */
    protected function replaceTableWithAlias($column, $table, $alias)
    {
        return str_replace("{$table}.", "{$alias}.", $column);
    }

    /**
     * @param Builder $query
     * @param string                             $table             join the table
     * @param string                             $one               joins first parameter
     * @param string|array|null                  $operatorOrColumns operator condition or columns list
     * @param string                             $two               joins two parameter
     * @param string                             $type              join type (left, right, '', etc)
     * @param array                              $columns           if you will not pass columns, it will retrieve the
     *                                                              column listing. If you pass null it will not get
     *                                                              any data from the model.
     * @param callable|null                      $extendJoin        Closure that receives JoinClause as first parameter.
     * @param string|null                        $tableAlias
     *
     * @return Builder
     */
    public function scopeJoinWithSelect($query, $table, $one, $operatorOrColumns, $two, $type = 'left',
                                        $columns = array(), callable $extendJoin = null, $tableAlias = null)
    {
        $joinTableExpression = $table;
        if ($tableAlias === null) {
            $tableAlias = $table;
        } else {
            $joinTableExpression = DB::raw("`{$table}` as `{$tableAlias}`");
        }

        // if the operator columns are in
        if (is_array($operatorOrColumns) || is_null($operatorOrColumns)) {
            $columns = $operatorOrColumns;
            $operatorOrColumns = '=';
        }

        if (!is_null($columns)) {
            // if there is no specific columns, lets get all
            if (empty($columns)) {
                $columns = Schema::getColumnListing($table);
            }

            // build the table values prefixed by the table to ensure unique values
            foreach ($columns as $related_column) {
                $query->addSelect(new Expression("`$tableAlias`.`$related_column` AS `$tableAlias.$related_column`"));
            }
        }

        return $query->join(
            $joinTableExpression,
            function (JoinClause $join) use ($one, $two, $table, $tableAlias, $operatorOrColumns, $extendJoin) {
                // Add on condition
                $join->on(
                    $this->replaceTableWithAlias($one, $table, $tableAlias),
                    $operatorOrColumns,
                    $this->replaceTableWithAlias($two, $table, $tableAlias)
                );

                // Support custom JoinClause conditions
                if (is_callable($extendJoin)) {
                    $extendJoin($join);
                }
            }, null, null, $type);
    }

    /**
     * Overrides the basic attributes filling with check if the attributes has
     * columns with table format. Checks if we can make a model based on table prefix and
     * relation definition. Tested on BelongsTo and left join.
     *
     * @param array      $attributes
     * @param bool|false $sync
     *
     * @return mixed
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        // Find all attributes
        $tableAttributes = $this->getAttributesByTablePrefix($attributes);

        if (!empty($tableAttributes)) {
            foreach ($tableAttributes as $tableFull => $newAttributes) {
                // Get the table name method
                $table = $this->getBelongsToMethodName($tableFull);

                // check if exists
                if (method_exists($this, $table)) {
                    /** @var Relation $relation */
                    $relation = $this->$table();

                    // check if the relations support related method and we have
                    // relation object
                    if (is_object($relation) && method_exists($relation, 'getRelated')) {
                        // when joining via left, all the values can be null
                        // if not found, lets support null relation
                        $isAllNull = true;

                        // remove the attributes from the original model
                        foreach ($newAttributes as $key => $attribute) {
                            unset($attributes[$tableFull.'.'.$key]);
                            if (!is_null($attribute)) {
                                $isAllNull = false;
                            }
                        }

                        // if we have all values null, the object doesn't exists
                        if ($isAllNull) {
                            $instance = null;
                        } else {
                            // build the realation object
                            $instance = $relation->getRelated()->newFromBuilder($newAttributes);
                        }

                        // store the relation object
                        $this->setRelation($table, $instance);
                    }
                }
            }
        }

        return parent::setRawAttributes($attributes, $sync);
    }

    /**
     * Loops all the attributes and finds only values that have prefix format
     * TABLE.COLLUMN.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function getAttributesByTablePrefix(array $attributes)
    {
        $tableAttributes = array();

        foreach ($attributes as $attribute => $value) {
            // check prefix format
            if (preg_match("/([^\.]+)\.(.*)/", $attribute, $matches)) {
                $tableAttributes[$matches[1]][$matches[2]] = $value;
            }
        }

        return $tableAttributes;
    }

    /**
     * Returns the method name for given table.
     *
     * @param string $tableFull like activity_types
     *
     * @return string as activity_type
     */
    protected function getBelongsToMethodName($tableFull)
    {
        // check if its relation function. The table names can
        // be in plural
        $table = Str::singular($tableFull);

        // support the aliases for changing the table name
        // to shorted version
        if (isset($this->relationAliases[$table])) {
            return $this->relationAliases[$table];
        }

        return $table;
    }
}
