<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Trait CleanHTMLFromAttributeTrait
 *
 * Enables automatic attribute value cleaning from HTML for all attributes, by limiting only desired by
 * `$cleanAttributes` property or by limiting which attributes can have html `$dontCleanAttributes`.
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait CleanHTMLFromAttributeTrait
{
    use CleanHTMLTrait;

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function setAttribute($key, $value)
    {
        // call the parent attribute setting
        parent::setAttribute($key, $this->tryToCleanAttributeValue($key, $value));
    }

}