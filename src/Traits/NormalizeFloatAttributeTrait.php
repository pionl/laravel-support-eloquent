<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Enables the automatic normalizing of string value to float with comma support. You can provide
 * list of columns keys to allow only specified columns (use `$normalizeFloatAttributes`).
 * or as property.
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait NormalizeFloatAttributeTrait
{
    use NormalizeFloatTrait;

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        // call the parent attribute setting
        parent::setAttribute($key, $this->tryToNormalizeFloatAttributeValue($key, $value));
    }
}
