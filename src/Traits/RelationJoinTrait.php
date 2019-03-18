<?php

namespace Pion\Support\Eloquent\Traits;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

/**
 * Class RelationJoinTrait.
 *
 * Trait to create model join for scope with detection of model in the attributes.
 *
 * Prefils the relations array.
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param                                    $relationName
     * @param string                             $operatorOrColumns ON condition operator
     * @param string                             $type              join type (left, right, '', etc)
     * @param bool                               $where             custom where condition
     * @param array                              $columns           if you will not pass columns, it will retrieve the
     *                                                              column listing. If you pass null it will not get
     *                                                              any data from the model. all columns *
     *
     * @return \Illuminate\Database\Query\Builder
     *
     * @see http://laravel-tricks.com/tricks/automatic-join-on-eloquent-models-with-relations-setup
     */
    public function scopeModelJoin($query, $relationName, $operatorOrColumns = '=', $type = 'left',
                                   $where = false, $columns = array())
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
            $two = "{$relation->getParent()->getTable()}.{$relation->getForeignKey()}";
        }

        return $this->scopeJoinWithSelect(
            $query,
            $table,
            $one,
            $operatorOrColumns,
            $two,
            $type,
            $where,
            $columns,
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $table             join the table
     * @param string                             $one               joins first parameter
     * @param string|array|null                  $operatorOrColumns operator condition or colums list
     * @param string                             $two               joins two parameter
     * @param string                             $type              join type (left, right, '', etc)
     * @param bool|false                         $where             custom where condition
     * @param array                              $columns           if you will not pass columns, it will retreive the
     *                                                              column listing. If you pass null it will not get
     *                                                              any data from the model.
     * @param string|null                        $tableAlias
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinWithSelect($query, $table, $one, $operatorOrColumns, $two, $type = 'left', $where = false,
                                        $columns = array(), $tableAlias = null)
    {
        $joinTableExpression = $table;
        if ($tableAlias === null) {
            $tableAlias = $table;
        } else {
            $joinTableExpression = \DB::raw("`{$table}` as `{$tableAlias}`");
        }

        // if the operator columns are in
        if (is_array($operatorOrColumns) || is_null($operatorOrColumns)) {
            $columns = $operatorOrColumns;
            $operatorOrColumns = '=';
        }

        if (!is_null($columns)) {
            // if there is no specific columns, lets get all
            if (empty($columns)) {
                $columns = \Schema::getColumnListing($table);
            }

            // build the table values prefixed by the table to ensure unique values
            foreach ($columns as $related_column) {
                $query->addSelect(new Expression("`$tableAlias`.`$related_column` AS `$tableAlias.$related_column`"));
            }
        }

        return $query->join(
            $joinTableExpression,
            $this->replaceTableWithAlias($one, $table, $tableAlias),
            $operatorOrColumns,
            $this->replaceTableWithAlias($two, $table, $tableAlias),
            $type,
            $where
        );
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
        // find
        $tableAttributes = $this->getAttributesByTablePrefix($attributes);

        if (!empty($tableAttributes)) {
            foreach ($tableAttributes as $tableFull => $newAttributes) {
                // get the tabale name method
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
