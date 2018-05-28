<?php

namespace Pion\Support\Eloquent\Traits;

use Carbon\Carbon;

/**
 * Trait DateAttributeTrait
 *
 * Converts any date string to carbon without fixed format. Extends the getAttributeValue function.
 *
 * @property array dateAttributes
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait DateAttributeTrait
{
    /**
     * Converts the value to Carbon if allowed
     * @param string $key
     * @param string $value
     *
     * @return Carbon|string
     */
    public function tryToConvertAttributeValueToDate($key, $value)
    {
        if ($this->canConvertValueToDate($key) && is_string($value)) {
            return Carbon::parse($value);
        }
        return $value;
    }

    /**
     * Checks if given attribute can be converted to date
     *
     * @param $key
     *
     * @return boolean
     */
    public function canConvertValueToDate($key)
    {
        return property_exists($this, 'dateAttributes') && in_array($key, $this->dateAttributes);
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        // Call parent if the attribute is not allowed
        if ($this->canConvertValueToDate($key) === false) {
            return parent::getAttributeValue($key);
        }

        // Get the value and parse only non-null values
        $value = $this->getAttributeFromArray($key);
        if (is_null($value)) {
            return $value;
        }

        // Return the carbon instance
        return Carbon::parse($value);
    }
}