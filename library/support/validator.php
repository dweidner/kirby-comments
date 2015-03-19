<?php

namespace Comments\Support;

use A;
use V;
use Error;

/**
 * Data Validator
 *
 * Takes a data collection and a set of rules to perform validation checks on
 * each entry.
 *
 * The implementation of this class is heavily based on the Validator class of
 * the @link(http://www.laravel.com, Laravel framework) but adapted and
 * simplified match the nomenclature of the Kirby toolkit.
 *
 * @see         http://laravel.com/docs/validation
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Support
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Validator {

  /**
   * A collection of validation rules.
   *
   * @var  array
   */
  protected $rules;

  /**
   * Collection of data attributes to validate.
   *
   * @var  array
   */
  protected $data;

  /**
   * A collection of error messages.
   *
   * @var  array
   */
  protected $messages;

  /**
   * A collection of validation errors.
   *
   * @var  Comments\Support\Messages
   */
  protected $errors;

  /**
   * Constructor.
   *
   * Create a new validator instance.
   *
   * @param  array  $data     Data attributes to validate
   * @param  array  $rules    Validation rules to apply.
   * @param  array  $mesages  Error messages to return.
   */
  public function __construct(array $data, array $rules, array $messages = array()) {

    $this->data     = $data;
    $this->rules    = array();
    $this->messages = array();
    $this->errors   = new Messages();

    $this->rules($rules);
    $this->messages($messages);

  }

  /**
   * Get/set the validation rules to apply.
   *
   * @param   array  $rules  Rules to apply.
   * @return  array|self
   */
  public function rules($rules = null) {

    if (is_null($rules)) {
      return $this->rules;
    }

    foreach ($rules as $attribute => &$rule) {
      $rule = is_string($rule) ? explode('|', $rule) : $rule;
    }

    $this->rules = array_merge($this->rules, $rules);
    return $this;

  }

  /**
   * Get/set error messages to display for validation errors.
   *
   * @param   array  $messages  Messages to use.
   * @return  array
   */
  public function messages($messages = null) {

    if (is_null($messages)) {
      return $this->messages;
    }

    $this->messages = array_merge($this->messages, $messages);
    return $this;

  }

  /**
   * Return the error collection.
   *
   * @return  Comments\Support\Messages
   */
  public function errors() {
    return $this->errors;
  }

  /**
   * Determine if the input data passes all given validation rules.
   *
   * @return  boolean
   */
  public function passes() {
    return count( $this->invalid() ) === 0;
  }

  /**
   * Determine if any input data fails the validation test.
   *
   * @return  boolean.
   */
  public function fails() {
    return !$this->passes();
  }

  /**
   * Get all data fields passing the rules.
   *
   * @return  array
   */
  public function valid() {

    $errors = $this->invalid();
    return array_diff(array_keys($this->rules), $errors);

  }

  /**
   * Get all data fields not passing the rules.
   *
   * @return  array
   */
  public function invalid() {

    // Iterate each field
    foreach ($this->rules as $attribute => $rules) {

      foreach($rules as $rule) {

        $args = array();

        // Extract optional arguments from a rule
        if ( strpos($rule, ':') !== false ) {
          $args = explode(':', $rule);
          $rule = reset($args);
          $args = array_slice($args, 1);
        }

        // Perform field validation
        $value = a::get($this->data, $attribute, '');
        $valid = $this->validate($attribute, $rule, $args);

        // Test if the field is required or optional
        $required = ( 'required' === $rule );
        $optional = ( 'required' !== $rule );

        // Given field value is required but not valid
        if ( !$valid && $required ) {
          $this->addMessage($attribute, $rule);
        }

        // Given field is optional, but invalid
        else if ( !$valid && $optional && !empty($value) ) {
          $this->addMessage($attribute, $rule);
        }

        // Given field depends on another validation field. Check if it is
        // available.
        else if ( !$valid && in_array( $rule, array('requiredWith', 'requiredWithout') ) ) {

          $dependency = reset($args);

          if (
            'requiredWith'    === $rule && !empty($this->data[$dependency]) ||
            'requiredWithout' === $rule &&  empty($this->data[$dependency])
          ) {
            $this->addMessage($attribute, $rule);
          }

        }

      }

    }

    return array_keys( $this->errors->all() );

  }

  /**
   * Validate the given field value.
   *
   * @throws  Error If validator does not exist.
   *
   * @param   string  $attribute  Name of the field to validate.
   * @param   string  $rule       Rule to validator.
   * @param   array   $args       Optional arguments.
   *
   * @return  boolean
   */
  protected function validate($attribute, $rule, $args = array()) {

    if (!isset(v::$validators[$rule])) {
      throw new Error("Validator [$rule] does not exist");
    }

    if ('required' === $rule) {
      array_unshift($args, $attribute, $this->data);
    } else if (in_array($rule, array('requiredWith', 'requiredWithout'))) {
      array_unshift($args, $attribute);
      array_push($args, $this->data);
    } else {
      $value = a::get($this->data, $attribute, '');
      array_unshift($args, $value);
    }

    return call_user_func_array(v::$validators[$rule], $args);

  }

  /**
   * Register an error during the validation test.
   *
   * @param   string  $attribute  Name of the field.
   * @param   string  $rule       Rule that failed.
   */
  protected function addMessage($attribute, $rule) {

    $a = Str::snakecase($attribute);
    $r = Str::snakecase($rule);

    $message = a::get($this->messages, "{$a}.{$r}", a::get($this->messages, $rule));

    if (empty($message)) $message = l("comments.validator.{$a}.{$r}");
    if (empty($message)) $message = l("comments.validator.{$r}");
    if (empty($message)) $message = l('comments.validator.fallback', 'Invalid value for {key}');

    $this->errors->add($attribute, $message);

  }

}
