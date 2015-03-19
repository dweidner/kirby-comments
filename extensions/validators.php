<?php

/**
 * Required (Conditionally)
 *
 * Field has to be present only if the other specified field IS present.
 *
 * @param   string  $key         Name of the field under validation.
 * @param   string  $dependency  Name of the field the actual field depends on.
 * @param   array   $array       Collection of data values.
 *
 * @return  boolean
 */
v::$validators['requiredWith'] = function($key, $dependency, $array) {
  return !empty($array[$dependency]) && v::required($key, $array);
};

/**
 * Required (Conditionally)
 *
 * Field has to be present only if the other specified field IS NOT present.
 *
 * @param   string  $key         Name of the field to test.
 * @param   string  $dependency  Name of the field the actual field depends on.
 * @param   array   $array       Collection of data values.
 *
 * @return  boolean
 */
v::$validators['requiredWithout'] = function($key, $dependency, $array) {
  return !empty($array[$dependency]) || v::required($key, $array);
};

/**
 * Username Validator
 *
 * Ensures the given user name exists in the system.
 *
 * @param mixed  $value  Value to test.
 * @return  boolean
 */
v::$validators['user'] = function($value) {
  return v::alphanum($value) && kirby()->site()->users()->find($value);
};
