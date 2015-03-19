<?php

namespace Comments;

use C;
use F;
use Dir;
use Str;
use stdClass;

use Comments\Container;

/**
 * Plugin Configuration
 *
 * A helper class to maintain plugin configuration.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Config extends stdClass {

  /**
   * Base directory containig default settings.
   *
   * @var  string
   */
  protected $root;

  /**
   * Plugin namespace.
   *
   * @var  string
   */
  protected $namespace;

  /**
   * Plugin configuration.
   *
   * @var  array
   */
  protected $data;

  /**
   * Configuration defaults.
   *
   * @var  array.
   */
  protected $defaults;

  /**
   * Constructor.
   *
   * Create a new object instance to maintain plugin configuration.
   *
   * @param  string  $root       Base directory containing default settings.
   * @param  string  $namespace  Plugin configuration namespace.
   */
  public function __construct($root, $namespace) {

    $this->root = $root;
    $this->namespace = $namespace;

  }

  /**
   * Retrieve a configuration value.
   *
   * @param   string  $key
   * @param   mixed   $default
   *
   * @return  mixed
   */
  public function get($key = null, $default = null) {

    // Lazy initialize configuration collection.
    if (is_null($this->data)) {
      $this->data = $this->load();
    }

    // Retrieve the entire collection if no key is given.
    if (is_null($key)) {
      return $this->data;
    }

    // Retrieve a single configuration value from the configuration.
    $keys  = explode('.', $key);
    $value = $this->data;

    foreach ($keys as $k) {

        // No element available with the given key
        if (!is_array($value) || !array_key_exists($k, $value)) {
          return $default;
        }

        $value = $value[$k];

    }

    return $value;

  }

  /**
   * Set configuration value.
   *
   * @param  string  $key
   * @param  mixed   $value
   */
  public function set($key, $value = null) {

    if (is_array($key)) {

      foreach ($key as $k => $v) {
        $this->data[$k] = $v;
        c::set($this->namespace . '.' . $k, $v);
      }

      return $this;

    }

    $this->data[$key] = $value;
    c::set($this->namespace . '.' . $key, $value);

    return $this;

  }

  /**
   * Load plugin options.
   *
   * @return array
   */
  public function load() {

    // Retrieve all plugin options from the configuration starting with a
    // prefix matching the plugin name
    $prefix = $this->namespace . '.';
    $keys = array_keys(c::$data);
    $keys = array_filter($keys, function($key) use ($prefix) {
      return str::startsWith($key, $prefix);
    });

    // Remove prefix and collect data
    $options = array();
    foreach($keys as $key) {
      $option = str::substr($key, str::length($prefix));
      $options[$option] = c::$data[$key];
    }

    // Merge plugin settings with defaults
    $defaults = $this->defaults();
    if (is_array($defaults) && !empty($defaults)) {
      $options = array_merge($defaults, $options);
    }

    return $options;

  }

  /**
   * Refresh plugin configuration.
   *
   * @return  array
   */
  public function refresh() {
    $this->data = $this->load();
  }

  /**
   * Get default values for all plugin options.
   *
   * @return  array
   */
  public function defaults() {

    // Load default options from the configuration folder, but only once.
    if (is_null($this->defaults)) {
      $this->defaults = include( realpath( $this->root . DS . 'config.php' ) );
    }

    return $this->defaults;

  }

  /**
   * Convert plugin configuration to array representation.
   *
   * @return  array
   */
  public function toArray() {
    return (array)$this;
  }

}
