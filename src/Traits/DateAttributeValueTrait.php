<?php

namespace Pion\Support\Eloquent\Traits;

trait DateAttributeValueTrait
{
    use DateAttributeTrait;

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        // call the parent attribute setting
        parent::setAttribute($key, $this->tryToConvertAttributeValueToDate($key, $value));
    }
}