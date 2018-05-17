<?php

/**
 * Runs all functions with the $key and $value - supports running multiple value altering.
 *
 * @param string                              $key
 * @param mixed                               $value
 * @param \Illuminate\Database\Eloquent\Model $object
 * @param array                               $functions An array with function names that will be called on object.
 *                                                       All
 *                                                       functions must have $key as first parameter, $value as second.
 *                                                       Must return the value.
 *
 * @return mixed
 */
function alter_attribute_value($key, $value, $object, array $functions)
{
    // Loop all functions and call the function with key and value
    foreach ($functions as $function) {
        $value = call_user_func_array([$object, $function], [$key, $value]);
    }
    return $value;
}