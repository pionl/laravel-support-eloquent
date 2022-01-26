<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Trait CleanHTMLTrait
 *
 * Enables attribute value cleaning from HTML for all attributes, by limiting only desired by
 * `$cleanAttributes` property or by limiting which attributes can have html `$dontCleanAttributes`.
 *
 * @property array       cleanAttributes
 * @property array       dontCleanAttributes
 * @property string|null stripHtmlTags You can use the property to specify tags which should
 * not be stripped.
 *
 * @package App\Models\Traits
 */
trait CleanHTMLTrait
{
    /**
     * Checks if given attribute should be striped from HTML. If no cleanAttributes or dontCleanAttributes
     * are not defined, all values will be cleaned.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function tryToCleanAttributeValue($key, $value)
    {
        $stripHtmlTags = $this->stripHtmlTags !== null && property_exists($this, $this->stripHtmlTags)
            ? $this->stripHtmlTags
            : null;

        // should we null the attribute
        if ($this->canRemoveHTMLFromAttribute($key, $value)) {
            return strip_tags($value, $stripHtmlTags);
        }
        return $value;
    }

    /**
     * Checks the given attribute can be cleaned.
     *
     * @param string $key
     * @param string $value
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