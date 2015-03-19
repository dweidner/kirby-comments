<?php

namespace Comments\Support;

use Str as KirbyStr;

/**
 * String Utility Class
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Support
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Str {

  /**
   * Convert a string to camel case notation.
   *
   * @param   string  $str  String to convert.
   * @return  string
   */
  public static function camelcase($str) {
    return str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $str)));
  }

  /**
   * Convert a string from camel case notation to a underscore representation.
   *
   * @param   string  $str  String to convert.
   * @return  string
   */
  public static function snakecase($str) {
    $str = preg_replace('/([a-z])([A-Z])/', '$1_$2', $str);
    return strtolower($str);
  }

  /**
   * Return the plural of the input string if quantity is larger than one.
   *
   * NOTE: This function will not handle any special cases.
   *
   * @see     http://www.oxforddictionaries.com/words/plurals-of-nouns
   *
   * @param   string   $singular  Singular noun
   * @param   integer  $quantity  Quantity
   * @param   string   $plural    Plural form
   *
   * @return  string
   */
  public static function plural($singular, $quantity = 2, $plural = null) {

    if ($quantity <= 1 || empty($singular))
      return $singular;

    if (!is_null($plural))
      return $plural;

    $last = str::lower( $singular[ str::length($singular) - 1 ] );
    $lastTwo = str::lower( substr($singular, 0, -2) );

    if ('y' === $last) {
      return substr($singular, 0, -1) . 'ies';
    } else if ('f' === $last || 'fe' === $lastTwo) {
      return $singular . 'ves';
    } else if (in_array($last, array('s', 'x', 'z'))) {
      return substr($singular, 0, -1) . 'es';
    } else if (in_array($lastTwo, array('ch', 'sh'))) {
      return substr($singular, 0, -2) . 'es';
    } else {
      return $singular . 's';
    }

  }

  /**
   * Handle dynamic static method calls.
   *
   * @param   string  $method
   * @param   array   $arguments
   * @return  mixed
   */
  public static function __callStatic($method, $arguments) {
    return call_user_func_array(array('Str', $method), $arguments);
  }

}
