<?php
namespace Pion\Support\Eloquent\Traits;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

/**
 * Class RelationJoinTrait
 *
 * Trait to create model join for scope with detection of model in the attributes.
 *
 * Prefils the relations array.
 *
 *  @property array $relationAliases A list of relations that has different method name than the table. Can be defined
 * in model like this:
 * protected $relationAliases = [
 *   "activity_type" => "type"
 *   ];
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait RelationJoinTrait
{
    
    /**
     * This determines the foreign key relations automatically to prevent the need to figure out the columns.
     *
     * Based on http://laravel-tricks.com/tricks/automatic-join-on-eloquent-models-with-relations-setup
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string    $relation_name          the function that return the relation
     * @param string    $operatorOrCollumns     ON condition operator
     * @param string    $type                   join type (left, right, '', etc)
     * @param bool      $where                  custom where condition
     * @param array     $collumns               if you will not pass collumns, it will retreive the collumn listing.
     * If you pass null
     * it will not get any data from the model.
     * all collumns *
     *
     * @return \Illuminate\Database\Query\Builder
     *
     * @link http://laravel-tricks.com/tricks/automatic-join-on-eloquent-models-with-relations-setup
     */
    public function scopeModelJoin($query, $relation_name, $operatorOrCollumns = '=', $type = 'left',
                                   $where = false, $collumns = array()) {

        $relation = $this->$relation_name();
        $table = $relation->getRelated()->getTable();
        $one = $relation->getRelated()->getQualifiedKeyName();
        $two = $relation->getForeignKey();

        return $this->scopeJoinWithSelect($query, $table, $one, $operatorOrCollumns, $two, $type, $where, $collumns);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $table         join the table
     * @param string $one           joins first parameter
     * @param string|array|null $operatorOrCollumns    operator condition or collums list
     * @param string $two           joins two parameter
     * @param string $type          join type (left, right, '', etc)
     * @param bool|false $where     custom where condition
     * @param array $collumns       if you will not pass collumns, it will retreive the collumn listing. If you pass null
     * it will not get any data from the model.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinWithSelect($query, $table, $one, $operatorOrCollumns, $two, $type = "left", $where = false,
                                        $collumns = array())
    {
        // if the operator collumns are in
        if (is_array($operatorOrCollumns) || is_null($operatorOrCollumns)) {
            $collumns = $operatorOrCollumns;
            $operatorOrCollumns = "=";
        }

        if (!is_null($collumns)) {
            // if there is no specific collumns, lets get all
            if (empty($collumns)) {
                $collumns = \Schema::getColumnListing($table);
            }

            // build the table values prefixed by the table to ensure unique values
            foreach ($collumns as $related_column) {
                $query->addSelect(new Expression("`$table`.`$related_column` AS `$table.$related_column`"));
            }
        }

        return $query->join($table, $one, $operatorOrCollumns, $two, $type, $where);
    }

    /**
     * Overides the basic attributes filling with check if the attributes has
     * collumns with table format. Checks if we can make a model based on table prefix and
     * relation definition. Tested on BelonstTo and left join
     *
     * @param array $attributes
     * @param bool|false $sync
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
                    $relation = $this->$table();

                    if (is_object($relation) && method_exists($relation, "getRelated")) {

                        $instance = $relation->getRelated()->newFromBuilder($newAttributes);

                        $this->setRelation($table, $instance);

                        foreach ($newAttributes as $key => $attribute) {
                            unset($attributes[$tableFull.".".$key]);
                        }
                    }
                }
            }
        }

        return parent::setRawAttributes($attributes, $sync);
    }

    /**
     * Loops all the attributes and finds only values that have prefix format
     * TABLE.COLLUMN
     * @param array $attributes
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
     * Returns the method name for given table
     *
     * @param string $tableFull     like activity_types
     *
     * @return string   as activity_type
     */
    protected function getBelongsToMethodName($tableFull)
    {
        // check if its relation function. The table names can
        // be in plurar
        $table = Str::singular($tableFull);

        // support the aliases for changing the table name
        // to shorted version
        if (isset($this->relationAliases[$table])) {
            return $this->relationAliases[$table];
        }

        return $table;
    }
}