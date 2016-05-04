<?php
namespace Pion\Support\Eloquent\Traits;

/**
 * Class NullEmptyStringAttributeTrait
 *
 * Enables the automatic nulling of empty string value (like from post or set). You can provide
 * list of columns keys to allow only specified columns (use `$nullEmptyColumns`) to be nulled or you can provide a
 * list of columns keys to ignore while trying to null the value (use `$nullIgnoreColumns`).
 *
 * Usage for setting nullEmptyCollumns:
 *
 * public function __construct(array $attributes = []) {
 *      parent::__construct($attributes);
 *
 *       $this->nullEmptyColumns = [
 *          "name"
 *       ];
 * }
 */
trait NullEmptyStringAttributeTrait
{

    /**
     * Enables settings specified columns
     * @var array|null
     */
    protected $nullEmptyColumns = null;

    /**
     * Enables to set specified columns that should be ignored in seting null
     * @var array|null
     */
    protected $nullIgnoreColumns = null;

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function setAttribute($key, $value)
    {

        // should we null the attribute
        if ($this->canNullAttributeValue($key, $value)) {
            $value = null;
        }

        // call the parent attribute seting
        parent::setAttribute($key, $value);
    }

    /**
     * Checks the given attribute with value, detects if the value should
     * be nulled on empty string
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function canNullAttributeValue($key, $value)
    {
        // filter collumns (optional setting)
        if (is_array($this->nullEmptyColumns) && !in_array($key, $this->nullEmptyColumns)) {
            return false;
        }

        // filter attributes that are in ignore
        if (is_array($this->nullIgnoreColumns) && in_array($key, $this->nullIgnoreColumns)) {
            return false;
        }

        // check if the value is string and the trimed value is empty
        // first check without trim for non needed trim call
        return is_string($value) && ($value === "" || trim($value) === "");
    }
}