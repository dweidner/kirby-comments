<?php

namespace Comments\Support;

use I;

/**
 * Message Collection
 *
 * Represents a simple collection of messages that can be used to display user
 * feedback on the front-end.
 *
 * The implementation of this class is heavily based on the MessageBag class of
 * the @link(http://www.laravel.com, Laravel framework) but adapted and
 * simplified to match the nomenclature of the Kirby toolkit.
 *
 * @see         http://laravel.com/docs/validation
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Support
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Messages extends I {

  /**
   * Add a new message to the collection.
   *
   * @param  string  $name     Name of the message.
   * @param  string  $message  Message text.
   */
  public function add($name, $message = null) {

    // Mass assign messages if array is given
    if(is_array($name)) {
      $this->data = array_merge($this->data, $name);
      return $this;
    }

    $this->data[$name] = $message;
    return $this;

  }

  /**
   * Determine if the collection is empty.
   *
   * @return  boolean
   */
  public function isEmpty() {
    return $this->count() === 0;
  }

  /**
   * Count the number of messages in the collection.
   *
   * @return  integer
   */
  public function count() {
    return count($this->data);
  }

  /**
   * Determine if a message exists for the given key.
   *
   * @param   string  $key
   * @return  boolean
   */
  public function has($key = null) {
    return $this->first($key) !== null;
  }

  /**
   * Get the message for a given key.
   *
   * @param   string  $key     Key of the message.
   * @param   string  $format  Optional formatting option.
   *
   * @return  mixed
   */
  public function get($key, $format = null) {

    if (isset($this->data[$key])) {
      return $this->format($key, $this->data[$key], $format);
    }

    return array();

  }

  /**
   * Get the first message in the collection.
   *
   * @param   string  $key     Optional key.
   * @param   string  $format  Optional formatting option.
   *
   * @return  mixed
   */
  public function first($key = null, $format = null) {

    $messages = is_null($key) ? $this->all($format) : $this->get($key, $format);
    return count($messages) > 0 ? $messages[0] : null;

  }

  /**
   * Get all messages in the collection.
   *
   * @param   string  $format  Optional formatting option.
   * @return  array
   */
  public function all($format = null) {

    $messages = array();
    foreach ($this->data as $key => $msg) {
      $messages[$key] = $this->format($key, $msg, $format);
    }

    return $messages;

  }

  /**
   * Merge the message collection with another instance.
   *
   * @param   Messages  $messages  Collection to merge with.
   * @return  self
   */
  public function merge(Messages $messages) {
    return $this->add( $messages->all() );
  }

  /**
   * Format a message similar to Kirby’s Str::template() method. Allowed
   * placeholders are {key} and {message}. Can be used to force an html output.
   *
   * Example:
   * echo $messages->all('<div>{message} - {key}</div>');
   *
   * Output:
   * <div>Message - A</div>
   * <div>Message - B</div>
   *
   * @param   string  $key      Message key.
   * @param   string  $message  Message to format.
   * @param   string  $format   Message format.
   *
   * @return  string
   */
  protected function format($key, $message, $format = null) {

    $subjects = array_filter( array($message, $format) );
    foreach ($subjects as $subject) {
      $message = str_replace(array('{key}', '{message}'), array($key, $message), $subject);
    }

    return $message;

  }

  /**
   * Json encode the message collection.
   *
   * @param   integer  $options
   * @return  string
   */
  public function toJson($options = 0) {
    return json_encode($this->toArray(), $options);
  }

  /**
   * Convert the collection of messages to an array.
   *
   * @return string
   */
  public function toArray() {
    return $this->data;
  }

  /**
   * Convert the collection of messages to a string representation.
   *
   * @return string
   */
  public function toString($format = null) {
    return implode($this->all($format));
  }

  /**
   * Convert the collection of messages to a string representation.
   *
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

}
