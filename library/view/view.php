<?php

namespace Comments\View;

use A;
use F;
use Error;

use Comments\Support\Messages;

/**
 * View
 *
 * @todo View class description
 *
 * @package     Kirby CMS
 * @subpackage  Comments\View
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class View {

  /** Error Codes */
  const ERROR_INVALID_VIEW = 0;

  /**
   * Collection of variables when rendering the view.
   *
   * @var  array
   */
  protected $scope;

  /**
   * Template file used to render the view.
   *
   * @var  string
   */
  protected $file;

  /**
   * A map of event types to event listeners.
   *
   * @var  array
   */
  protected $handlers;

  /**
   * Factory method. Create a new view instance base on the given template file.
   *
   * @param   string  $file  Template file to load.
   * @return  View
   */
  public static function make($file) {

    $view = new self($file);
    return $view;

  }

  /**
   * Constructor.
   *
   * Creates a new view instance.
   *
   * @param  string  $file   Template file to load.
   * @param  array   $scope  Variables to make accessible to the template.
   */
  public function __construct($file, $scope = array()) {

    $this->file($file);
    $this->scope = array();
    $this->handlers = array();

    foreach ($scope as $key => $value) {
      $this->with($key, $value);
    }

  }

  /**
   * Get or set the template file to use.
   *
   * @param   string       $file  Template file to render.
   * @return  string|self
   */
  public function file($file = null) {

    if (is_null($file)) {
      return $this->file;
    }

    if (!file_exists($file)) {
      throw new Error("View [$file] does not exist", self::ERROR_INVALID_VIEW);
    }

    $this->file = $file;
    return $this;

  }


  /**
   * Push a new variable to the view’s scope.
   *
   * @param  string  $key    Name of the variable.
   * @param  mixed   $value  Value of the variable.
   *
   * @return  self
   */
  public function with($key, $value = null) {

    if (is_array($key)) {
      foreach ($key as $k => $v) $this->scope[$k] = $v;
      return $this;
    }

    $this->scope[$key] = $value;
    return $this;

  }

  /**
   * Render the view with the given error collection.
   *
   * @param   Messages|array  $messages
   * @return  self
   */
  public function errors($messages) {

    $this->scope['errors'] = $messages instanceof Messages ? $messages : new Messages($messages);
    return $this;

  }

  /**
   * Register a partial to render with the view.
   *
   * @param   string       $key
   * @param   View|string  $view
   *
   * @return  self
   */
  public function nest($key, $view) {

    $view = is_a($view, 'View') ? $view : new View($view, $this->scope);
    return $this->scope[$key] = $view->with('parent', $this);

  }

  /**
   * Register an event handler for a given event type.
   *
   * @param   string    $event  Event type to listen to.
   * @param   function  $cb     Callback function
   *
   * @return  self
   */
  public function on($event, $cb) {

    if (!isset($this->handlers[$event])) {
      $this->handlers[$event] = array();
    }

    $this->handlers[$event][] = $cb;
    return $this;

  }

  /**
   * Trigger a given event type.
   *
   * @param   string  $event  Event type to trigger.
   * @return  boolean
   */
  public function trigger($event, $args = array()) {

    $handlers = a::get($this->handlers, $event, array());
    array_unshift($args, $this);

    foreach ($handlers as $callable) {
      if (!call_user_func_array($callable, $args)) return false;
    }

    return true;

  }

  /**
   * Get the generated view content.
   *
   * @return  string
   */
  public function content() {

    ob_start();
      f::load($this->file, $this->scope);
      $output = ob_get_contents();
    ob_end_clean();

    return $output;

  }

  /**
   * Render the view.
   *
   * @return string
   */
  public function render() {

    $this->trigger('alter');
    echo $this->content();

  }

  /**
   * Render the view.
   *
   * @return  string
   */
  public function toString() {
    return (string)$this;
  }

  /**
   * Render the view.
   *
   * @return string
   */
  public function __toString() {
    return $this->content();
  }

  /**
   * Handle dynamic method calls.
   *
   * @param   string  $method     Method to call.
   * @param   array   $arguments  Method arguments.
   *
   * @return  mixed
   */
  public function __call($method, $arguments) {

    if (empty($arguments)) {
      return isset($this->scope[$method]) ? $this->scope[$method] : null;
    }

    return call_user_func_array(array($this, 'with'), $arguments);

  }

}
