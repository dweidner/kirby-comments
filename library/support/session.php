<?php

namespace Comments\Support;

use A;
use S;

/**
 * Session Utility Class
 *
 * A helper class used to add a Flash to Kirby’s default session helper. A
 * Session Flash allows to write data to the session which will remain in the
 * Global only for the next request.
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Support
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Session {

  /**
   * Session key used for the flash registry.
   *
   * @var  string
   */
  public static $namespace = 'flash_registry';

  /**
   * Write data to the Session flash. Variables in the Session flash will remain
   * in the Session only for the next request.
   *
   * @param   mixed  $key    Name of the variable OR Collection of values to write.
   * @param   mixed  $value  Value to write to the session.
   *
   * @return  mixed|null
   */
  public static function flash($key, $value = null) {

    // Make sure the session is started
    s::start();

    // Retrieve the flash data
    $registry = s::get(self::$namespace, array());

    // Write a collection of key-value pairs to the session flash
    if (is_array($key)) {

      foreach ($key as $k => $v) {
        $registry[$k] = 0;
        s::set($k, $v);
      }

    }

    // Write an individual key-value pair to the session flash
    else {

      $registry[$key] = 0;
      s::set($key, $value);

    }

    // Write registry back to the session global
    s::set(self::$namespace, $registry);

  }

  /**
   * Remove old values from the Session’s flash data.
   */
  public static function flush() {

    // Make sure the session is started
    s::start();

    // Retrieve the flash data
    $registry = s::get(self::$namespace);

    // Clean up registry
    if (!empty($registry)) {

      foreach ($registry as $key => $expiry) {

        $expiry++;

        // Remove all old values from the session
        if ($expiry > 1) {

          s::remove($key);
          unset($registry[$key]);

        }

        // Update remaining entries
        else {
          $registry[$key] = $expiry;
        }

      }

      // Write registry back to session
      if (!empty($registry)) {
        s::set(self::$namespace, $registry);
      }

      // Remove empty registry from session
      else {
        s::remove(self::$namespace);
      }

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
    return call_user_func_array(array('S', $method), $arguments);
  }

}

// Clean up old entries in the sessions flash registry
register_shutdown_function( array('Comments\Support\Session', 'flush') );
