<?php

namespace Comments;

use Error;
use Closure;

/**
 * Service Container
 *
 * @todo Service container class description
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Container {

  /**
   * Collection of factory methods.
   *
   * @var  array
   */
  protected $factories;

  /**
   * Collection of service definitions.
   *
   * @var  array
   */
  protected $instances;

  /**
   * Registry containing names of services resolved.
   *
   * @var  array
   */
  protected $resolved;

  /**
   * Constructor.
   *
   * Instantiate a new service container.
   */
  public function __construct() {

    $this->factories = array();
    $this->instances = array();
    $this->resolved = array();

  }

  /**
   * Add a service factory to the container.
   *
   * @param  string    $id        Name of the service.
   * @param  Callable  $callable  Service definition.
   */
  public function add($id, $callable) {

    if (!($callable instanceof Closure)) {
      throw new Error('Invalid service definition');
    }

    $this->factories[$id] = $callable;
    return $this;

  }

  /**
   * Add a service definition which creates a singleton instance.
   *
   * @param   string  $id        Name of the service to add.
   * @param   mixed   $callable  Service definition.
   *
   * @return  static
   */
  public function singleton($id, $callable) {

    if (!($callable instanceof Closure)) {
      throw new Error('Invalid service definition');
    }

    if (isset($this->resolved[$id])) {
      throw new Error("Cannot override locked service [$id]");
    }

    $this->instances[$id] = $callable;
    return $this;

  }

  /**
   * Add a parameter to the service container. Can be useful for factories
   * depending on specific configuration values.
   *
   * @param   string  $key    Name of the parameter.
   * @param   mixed   $value  Value of the parameter.
   *
   * @return  static
   */
  public function param($key, $value) {

    if ($value instanceof Closure) {
      throw new Error('Closures can not be used as parameter value.');
    }

    $this->instances[$key] = $value;
    return $this;

  }

  /**
   * Check if a service or parameter is added to the container.
   *
   * @param   string   $id  Name of the service/parameter.
   *
   * @return  boolean       [description]
   */
  public function has($id) {
    return isset($this->instances[$id]) || isset($this->factories[$id]);
  }

  /**
   * Retrieve a resolved service instance or service parameter from the
   * container.
   *
   * @param   string  $id  Name of the service/parameter.
   * @return  mixed
   */
  public function get($id) {

    if (!$this->has($id)) {
      throw new Error("Service does not exist [$id]");
    }

    // Return a resolved singleton instance or a parmater value
    if (
      isset($this->resolved[$id]) ||
      !is_object($this->instances[$id]) ||
      !method_exists($this->instances[$id], '__invoke')
    ) {
      return $this->instances[$id];
    }

    // Create a new service instance if calling a factory
    if (isset($this->factories[$id])) {
      return $this->factories[$id]($this);
    }

    // Resolve a singleton instance
    $singleton = $this->instances[$id];
    $value = $singleton($this);

    // Register singleton value
    $this->instances[$id] = $value;
    $this->resolved[$id] = true;

    return $value;

  }

  /**
   * Remove a service or parameter from the container.
   *
   * @param   string  $id  Name of the service/parameter.
   * @return  static
   */
  public function remove($id) {

    if ($this->has($id)) {
      unset($this->factories[$id], $this->instances[$id], $this->resolved[$id]);
    }

    return $this;

  }

  /**
   * Get the name of all registered services.
   *
   * @return  array
   */
  public function keys() {

    return array_merge(
      array_keys($this->factories),
      array_keys($this->instances)
    );

  }

  /**
   * Number of services registered.
   *
   * @return  integer
   */
  public function count() {
    return count($this->factories) + count($this->instances);
  }

  /**
   * Handle dynamic method calls.
   *
   * @param  string  $method
   * @param  array   $arguments
   *
   * @return mixed
   */
  public function __call($method, $arguments) {
    return $this->get($method);
  }

}
