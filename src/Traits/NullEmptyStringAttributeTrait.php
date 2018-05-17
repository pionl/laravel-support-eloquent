<?php

namespace Pion\Support\Eloquent\Traits;

/**
 * Trait NullEmptyStringAttributeTrait
 *
 * Enables the automatic nulling of empty string value (like from post or set). You can provide
 * list of columns keys to allow only specified columns (use `$nullEmptyAttributes`) to be set to null or you can provide a
 * list of columns keys to ignore while trying to null the value (use `$dontNullEmptyAttributes`). They can be set in construct
 * or as property.
 *
 * @package Pion\Support\Eloquent\Traits
 */
trait NullEmptyStringAttributeTrait
{
    use NullEmptyStringTrait;

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        // call the parent attribute setting
        parent::setAttribute($key, $this->tryToNullAttributeValue($key, $value));
    }
}