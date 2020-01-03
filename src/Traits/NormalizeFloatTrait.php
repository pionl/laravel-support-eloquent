<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Exposes method for detecting which attribute should be set to float. You can provide
 * list of columns keys to allow only specified columns (use `$normalizeFloatAttributes`) to be set normalized
 * to float with comma support.
 *
 * `$normalizeFloatAttributes` must be set via property.
 *
 * @property array $normalizeFloatAttributes
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait NormalizeFloatTrait
{
    /**
     * Return null value if value should be set to null
     *
     * @param string $key
     * @param        $value
     *
     * @return null
     */
    public function tryToNormalizeFloatAttributeValue($key, $value)
    {
        // should we null the attribute
        if ($this->canNormalizeFloatAttribute($key, $value)) {
            return floatval(str_replace(',','.', $value));
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
    public function canNormalizeFloatAttribute($key, $value)
    {
        if (property_exists($this, 'normalizeFloatAttributes') &&
            is_array($this->normalizeFloatAttributes) && in_array($key, $this->normalizeFloatAttributes)) {
            return $value !== null;
        }

        return false;
    }
}
