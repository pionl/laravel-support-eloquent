<?php

namespace Pion\Support\Eloquent\Traits;

use Carbon\Carbon;

/**
 * Trait DateAttributeTrait
 *
 * Converts any date string to carbon without fixed format. Extends the getAttributeValue function.
 *
 * @property array        dateAttributes
 * @property array|string dateFormats Date formats indexed by attribute name
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
        if ($this->canConvertValueToDate($key)) {
            return $this->tryToConvertToDateString($key, $value);
        }
        return $value;
    }

    /**
     * Tries to convert the value to valid date string in correct date format based on attribute
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return string|mixed
     */
    public function tryToConvertToDateString($key, $value)
    {
        if (is_null($value)) {
            return null;
        }
        if (is_string($value) && $value !== '') {
            return $this->asValidDateString($key, $value);
        }
        if ($value instanceOf Carbon) {
            return $value->format($this->getDateFormatFor($key));
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
        if (false === $this->canConvertValueToDate($key)) {
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

    /**
     * Returns date format for given attribute.
     *
     * @param string $attribute
     *
     * @return string
     */
    protected function getDateFormatFor($attribute)
    {
        if (false === property_exists($this, 'dateFormats')) {
            return $this->getDateFormat();
        }
        // Support global date format
        if (is_string($this->dateFormats)) {
            return $this->dateFormats;
        }
        // Support individual date format
        if (false === isset($this->dateFormats[$attribute])) {
            return $this->getDateFormat();
        }
        return $this->dateFormats[$attribute];
    }

    /**
     * Parsers date string the date to carbon instance and back to desired date attribute format.
     *
     * @param string $key Attribute key
     * @param string $value
     *
     * @return string
     */
    protected function asValidDateString($key, $value)
    {
        $dateFormat = $this->getDateFormatFor($key);
        // Remove space to support dates with spaces (28. 08. 2018)
        return Carbon::parse(str_replace(' ', '', $value))->format($dateFormat);
    }
}