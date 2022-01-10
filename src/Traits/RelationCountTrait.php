<?php
namespace Pion\Support\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait RelationCountTrait
 *
 * Usage of where: $count = $model->relationCountWithWhere("user_permission", "user_id", $user, "App\\Models\\User");
 *
 *
 * Calling the function again will use the cache in relations array. After this call you can also use
 * $model->user_permission_{ForeignKey}_{userIdValueForWhere} which will the object of User model with count attribute.
 *
 * You can also get the where index by passing variable which will be overided by the reference:
 * $index = "user_permission";
 * $model->relationCountWithWhere($index, "user_id", $user, "App\\Models\\User");
 *
 * $model->relationCount("user", "App\\Models\\User") will return count and the index will be only $model->user
 * 
 * @package Pion\Support\Eloquent\Traits
 */
trait RelationCountTrait {

    /**
     * Returns a model with caching posibility
     *
     * @param string $index         a index that will be used as index
     * @param \Closure $function     a function that will build the query if needed
     *
     * @return Model
     */
    protected function relationCountQueryObjectWithIndex($index, $function)
    {

        // if relation is not loaded already, let's do it first
        if ( ! property_exists($this, $index)) {
            $this->relations[$index] = $function()->first();
        }

        return $this->$index;
    }

    /**
     * Creates basic a realtion query
     *
     * @param string $related       a related object to create
     * @param string $foreignKey    a foreign key
     * @param string $localKey      local key for the connection
     * @return HasOne
     */
    protected function relationCountQuery($related, $foreignKey = null, $localKey = null) {
        $relation = $this->hasOne($related, $foreignKey, $localKey);
        $currentForeignKey = $relation->getForeignKeyName();

        // we must add a count, and also the the foreign key to enable returning the relation
        // which is paired by the key
        return $relation->selectRaw($currentForeignKey.', count('.$currentForeignKey.') as count')
            ->groupBy($currentForeignKey);
    }

    /**
     * Creates a relation query with where conditoon
     *
     * @param string        $collumn        a collumn for using where conditon
     * @param mixed         $value          a value for where condition
     * @param string        $related        a related object to create
     * @param string        $foreignKey     a foreign key
     * @param string        $localKey       local key for the connection
     *
     * @return HasOne
     */
    protected function relationCountQueryWhere($collumn, $value, $related, $foreignKey = null, $localKey = null) {
        return $this->relationCountQuery($related, $foreignKey, $localKey)->where($collumn, $value);
    }


    /**
     * Creates a relation query that will be cached in given index. This will return the whole object with count
     * attribute
     *
     * @param string $index         a index that will be used as index
     * @param string $related       a related object to create
     * @param string $foreignKey    a foreign key
     * @param string $localKey      local key for the connection
     * @return HasOne
     */
    public function relationCountObject($index, $related, $foreignKey = null, $localKey = null)
    {
        return $this->relationCountQueryObjectWithIndex($index, function() use ($related, $foreignKey, $localKey) {
            return $this->relationCountQuery($related, $foreignKey, $localKey);
        });
    }

    /**
     * Creates a relation query that is filtered with given where condition.
     * The result will be cached in given index. This will return the whole object with count
     * attribute. The index is altered with given collumn and value for different use.
     *
     * @param string &$index        the index of the where, the index will use the value for the key
     * @param string $collumn       a collumn for using where conditon
     * @param mixed $value          a value for where condition
     * @param string $related       a related object to create
     * @param string $foreignKey    a foreign key
     * @param string $localKey      local key for the connection
     *
     * @return Model
     */
    public function relationCountObjectWithWhere(&$index, $collumn, $value, $related, $foreignKey = null, $localKey = null)
    {
        // change the value if the value is object
        $currentValue = $value;

        if (is_object($value)) {
            $currentValue = $value->getKey();
        }

        $index.= $collumn."_".$currentValue;

        return $this->relationCountQueryObjectWithIndex($index,
            function () use ($collumn, $currentValue, $related, $foreignKey, $localKey) {
                return $this->relationCountQueryWhere($collumn, $currentValue, $related, $foreignKey, $localKey);
            });
    }

    /**
     * Returns a count for given relation cached in the given index
     *
     * @param string $index         a index that will be used as index
     * @param string $related       a related object to create
     * @param string $foreignKey    a foreign key
     * @param string $localKey      local key for the connection
     * @return int
     */
    public function relationCount($index, $related, $foreignKey = null, $localKey = null)
    {
        $relatedObject = $this->relationCountObject($index, $related, $foreignKey, $localKey);
        return $this->getRelationCountFromObject($relatedObject);
    }

    /**
     * Returns a count filtered by condition for given relation cached in the given index
     *
     * @param string &$index        the index of the where, the index will use the value for the key
     * @param string $collumn       a collumn for using where conditon
     * @param mixed $value          a value for where condition
     * @param string $related       a related object to create
     * @param string $foreignKey    a foreign key
     * @param string $localKey      local key for the connection
     *
     * @return int
     */
    public function relationCountWithWhere(&$index, $collumn, $value, $related, $foreignKey = null, $localKey = null)
    {
        $query = $this->relationCountObjectWithWhere($index, $collumn, $value, $related, $foreignKey, $localKey);
        return $this->getRelationCountFromObject($query);
    }

    /**
     * Returns the count from given relation count object
     *
     * @param HasOne $relatedObject
     * @return int
     */
    public function getRelationCountFromObject($relatedObject)
    {
        // then return the count directly
        return ($relatedObject) ? (int) $relatedObject->count : 0;
    }
}