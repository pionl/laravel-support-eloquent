<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Trait NullEmptyStringTrait
 * Exposes method for detecting which attribute should be set to null. You can provide
 * list of columns keys to allow only specified columns (use `$nullEmptyAttributes`) to be set to null or you can
 * provide a list of columns keys to ignore while trying to null the value (use `$dontNullEmptyAttributes`).
 * `$nullEmptyAttributes` and `$dontNullEmptyAttributes` must be set via property.
 *
 * @property array $nullEmptyAttributes
 * @property array $dontNullEmptyAttributes
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait NullEmptyStringTrait
{
    /**
     * Return null value if value should be set to null
     *
     * @param string $key
     * @param        $value
     *
     * @return null
     */
    public function tryToNullAttributeValue($key, $value)
    {
        // should we null the attribute
        if ($this->canNullAttributeValue($key, $value)) {
            return null;
        }
        return $value;
    }

    /**
     * Checks the given attribute with value, detects if the value should
     * be set to null on empty string
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function canNullAttributeValue($key, $value)
    {
        // filter collumns (optional setting)
        if (property_exists($this, 'nullEmptyAttributes') &&
            is_array($this->nullEmptyAttributes) && !in_array($key, $this->nullEmptyAttributes)) {
            return false;
        }

        // filter attributes that are in ignore
        if (property_exists($this, 'dontNullEmptyAttributes') &&
            is_array($this->dontNullEmptyAttributes) && in_array($key, $this->dontNullEmptyAttributes)) {
            return false;
        }

        // check if the value is string and the trimed value is empty
        // first check without trim for non needed trim call
        return is_string($value) && ($value === "" || trim($value) === "");
    }
}