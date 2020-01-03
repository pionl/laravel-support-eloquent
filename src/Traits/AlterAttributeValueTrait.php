<?php

namespace Pion\Support\Eloquent\Traits;

trait AlterAttributeValueTrait
{
    use CleanHTMLTrait, NullEmptyStringTrait, DateAttributeTrait, NormalizeFloatTrait;

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, alter_attribute_value($key, $value, $this, [
            'tryToCleanAttributeValue',
            'tryToNullAttributeValue',
            'tryToConvertAttributeValueToDate',
            'tryToNormalizeFloatAttributeValue',
        ]));
    }
}
