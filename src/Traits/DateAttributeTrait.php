<?php

namespace Pion\Support\Eloquent\Traits;

use Carbon\Carbon;

/**
 * Trait DateAttributeTrait
 *
 * Converts any date string to carbon without fixed format. Extends the getAttributeValue function.
 *
 * @property array dateAttributes
 * @property array dateFormats Date formats indexed by attribute name
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait DateAttributeTrait
{
    /**
     * Converts the value to Carbon if allowed
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function tryToConvertAttributeValueToDate($key, $value)
    {
        if ($this->canConvertValueToDate($key) && is_string($value) && $value !== '') {
            return $this->convertAttributeToDate($value);
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
        return $this->convertAttributeToDate($value);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        // Handle json convert - we need to convert carbon objects to string
        $attributes = parent::attributesToArray();

        if (property_exists($this, 'dateAttributes') === false) {
            return $attributes;
        }

        foreach ($this->dateAttributes as $attribute) {
            if (isset($attributes[$attribute]) === false) {
                continue;
            }

            // Check if the value is carbon instance
            $value = $attributes[$attribute];
            if (($value instanceof Carbon) === false) {
                continue;
            }

            // Convert the carbon to date time and replace the value
            $format = $this->getDateFormatFor($attribute);
            $attributes[$attribute] = $value->format($format);
        }

        return $attributes;
    }

    /**
     * Returns date format for given attribute.
     *
     * @param string $attribute
     *
     * @return string
     */
    protected function getDateFormatFor($attribute)
    {
        if (property_exists($this, 'dateFormats') === false ||
            isset($this->dateFormats[$attribute]) === false) {
            return $this->getDateFormat();
        }
        return $this->dateFormats[$attribute];
    }

    /**
     * Converts the date to carbon instance. Parse any format
     *
     * @param string $value
     *
     * @return Carbon
     */
    protected function convertAttributeToDate($value)
    {
        // Remove space to support dates with spaces (28. 08. 2018)
        return Carbon::parse(str_replace(' ', '', $value));
    }
}