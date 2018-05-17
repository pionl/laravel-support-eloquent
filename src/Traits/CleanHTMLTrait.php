<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Trait CleanHTMLTrait
 *
 * Enables attribute value cleaning from HTML for all attributes, by limiting only desired by
 * `$cleanAttributes` property or by limiting which attributes can have html `$dontCleanAttributes`.
 *
 * @property array cleanAttributes
 * @property array dontCleanAttributes
 *
 * @package App\Models\Traits
 */
trait CleanHTMLTrait
{
    /**
     * @param $key
     * @param $value
     *
     * @return string
     */
    public function tryToCleanAttributeValue($key, $value)
    {
        // should we null the attribute
        if ($this->canRemoveHTMLFromAttribute($key, $value)) {
            return strip_tags($value);
        }
        return $value;
    }

    /**
     * Checks the given attribute can be cleaned.
     *
     * @param string $key
     * @param        $value
     *
     * @return bool
     */
    public function canRemoveHTMLFromAttribute($key, $value)
    {
        // filter collumns (optional setting)
        if (property_exists($this, 'cleanAttributes') &&
            is_array($this->cleanAttributes) && !in_array($key, $this->cleanAttributes)) {
            return false;
        }

        // filter attributes that are in ignore
        if (property_exists($this, 'dontCleanAttributes') && in_array($key, $this->dontCleanAttributes)) {
            return false;
        }

        return is_string($value);
    }
}