<?php

namespace Comments\View;

use A;
use C;
use Str;
use Url;
use User;
use Html;
use Brick;
use Collection;

use Comments\Support\Session;
use Comments\Support\Messages;
use Comments\Support\Validator;

/**
 * Comment Form
 *
 * This class represents the form used to create or edit comments in the
 * application. The class provides a default set of fields if none are given.
 * Further features are protection against Cross-Site Request forgery and built
 * in (server-side) field validation.
 *
 * @package     Kirby CMS
 * @subpackage  Comments\View
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Form extends Brick {

  /**
   * Tag to use for the form.
   *
   * @var  string
   */
  public $tag = 'form';

  /**
   * Collection of field definitions.
   *
   * @var  array
   */
  protected $fields;

  /**
   * Collection of form data.
   *
   * @var  array
   */
  protected $data;

  /**
   * Collection of validation messages.
   *
   * @var  Comments\Support\Messages
   */
  protected $messages;

  /**
   * Constructor
   *
   * Creates a new comment form instance.
   *
   * @param array  $fields  Collection of form fields.
   */
  public function __construct($fields = array()) {

    // Ensure the session is started
    Session::start();

    // Setup form container
    $this->addClass('form comment-form');

    // Set default action endpoint
    $hash = page()->hash();
    $this->method('post');
    $this->action( url("/api/pages/${hash}/comments/create") );

    // Provide default fields if none are given
    $this->fields = array();

    if (empty($fields)) {
      $this->defaults();
    } else {
      $this->fields($fields);
    }

    // Populate message collection from session
    $this->messages = new Messages();

    if ( $errors = Session::get('errors') ) {
      $this->messages->add( $errors );
    }

    // Collect field data
    $this->data = $this->input();

    // Perform form validation on submit
    $this->on('submit', function($form) {
      $form->validate();
    });

  }

  /**
   * Check if the user has provided us with valid values for all form fields.
   *
   * @return  boolean
   */
  public function isValid() {
    return $this->messages->isEmpty();
  }

  /**
   * Get/set the method to use for  data transmission (POST/GET).
   *
   * @param   string  $method
   * @return  self
   */
  public function method($method = null) {
    return $this->attr('method', $method);
  }

  /**
   * Get/set the action to perform on form submission.
   *
   * @param   string  $action
   * @return  self
   */
  public function action($action = null) {
    return $this->attr('action', $action);
  }

  /**
   * Trigger a form event.
   *
   * @param   string  $event  Name of the event to trigger.
   * @param   array   $args   Optional event arguments.
   *
   * @return  mixed
   */
  public function trigger($event, $args = array()) {

    $return  = null;
    $args    = !is_array($args) ? array($args) : $args;

    // Determine if someone is listening for the current event
    if(isset($this->events[$event])) {

      // Pass a reference of the form to the handler callback
      array_unshift($args, $this);

      // Call each event handler
      foreach($this->events[$event] as $handler) {
        $return = call_user_func_array($handler, $args);
      }

    }

    return $return;

  }

  /**
   * Append html to the form.
   *
   * @param   Brick|string  $html       (Html) element to append.
   * @param   Brick         $container  Container to add the element to first.
   * @return  self
   */
  public function append($field, $container = null) {

    // Try to retrieve the input name
    $name = null;
    if ($field instanceof Brick) {
      $name = $field->attr('name');
    }

    // Determine the parent container of the field
    $parent = null;
    if ($container !== $this) {
      $parent = $container;
    }

    // Allow the user to provide an inline message for each invalid field
    if ( $name && $this->messages->has($name) ) {
      $this->trigger('error', array($field, $parent));
    }

    // Allow the user to customize items added to the form
    if ($return = $this->trigger('render', array($field, $parent))) {
      $html = $return;
    }

    // Append the html to the form
    if ($parent instanceof Brick) {
      $parent->append($field);
      return parent::append($parent);
    }

    return parent::append($field);

  }

  /**
   * Register default comment form fields.
   */
  public function defaults() {

    if (!user::current()) {

      $this->field('author', array(
        'id'       => 'author',
        'type'     => 'text',
        'label'    => l('comments.field.author', 'Name'),
        'size'     => 30,
        'required' => true
      ));

      $this->field('author_email', array(
        'id'       => 'email',
        'type'     => 'email',
        'label'    => l('comments.field.author_email', 'E-Mail'),
        'size'     => 30,
        'required' => true,
        'rules'    => 'email',
      ));

      $this->field('author_url', array(
        'id'       => 'url',
        'type'     => 'url',
        'label'    => l('comments.field.author_url', 'Website'),
        'size'     => 30,
        'rules'    => 'url',
      ));

    } else {

      // Get currently logged in user and a link to the profile page
      $user  = user::current()->username();
      $link  = new Brick('a', $user, array(
        'href' => url('panel/#/users/edit/' . $user)
      ));

      // Add a paragraph showing the currently logged in user
      $this->append(
        new Brick('p', sprintf( l('comments.field.user', 'Logged in as %1$s'), $link ))
      );

    }

    $this->field('text', array(
      'id'       => 'text',
      'type'     => 'textarea',
      'label'    => l('comments.field.text', 'Comment'),
      'rows'     => 8,
      'cols'     => 45,
      'required' => true,
      'rules'    => 'min:5',
    ));

  }

  /**
   * Get the collection of form field definitions.
   *
   * @param   array  $fields
   * @return  array|self
   */
  public function fields($fields = null) {

    if (is_array($fields)) {

      foreach ($fields as $name => $field) {
        $this->field($name, $field);
      }

      return $this;

    }

    return $this->fields;

  }

  /**
   * Get/set the attribute definition for a field.
   *
   * @param   string  $name
   * @param   array   $definition
   *
   * @return  array
   */
  public function field($name, $definition = null) {

    if (!is_null($definition)) {

      $definition['name']     = $name;
      $definition['type']     = a::get($definition, 'type', 'text');
      $definition['default']  = a::get($definition, 'default', null);
      $definition['required'] = a::get($definition, 'required', false);
      $definition['rules']    = a::get($definition, 'rules', array());

      // Add a validation rule when the field is required.
      if ($definition['required']) {

        $search = 'required';
        $rules  = &$definition['rules'];

        if ( is_array($rules) && in_array($search, $rules) ) {
          array_unshift($rules, $search);
        } else if ( is_string($rules) && (false === strpos($rules, $search)) ) {
          $rules = $search . '|' . $rules;
        }

      }

      $this->fields[$name] = $definition;
      return $this;

    }

    return isset($this->fields[$name]) ? $this->fields[$name] : array();

  }

  /**
   * Collect all field values from the $_GET/$_POST global.
   *
   * @return  array
   */
  public function input() {

    $fields   = array_keys($this->fields);
    $values   = array();

    // Expect form input to be returned within the Session global if the form
    // is sent to an external script.
    if ( $this->attr('action') ) {

      $input  = Session::get('input', array());
      $input  = array_intersect_key( $input, array_flip($fields) );
      $values = array_merge( $values, $input );

    }

    // Otherwise try to retrieve the form data from the $_GET/$_POST globals.
    else {

      foreach($fields as $field) {
        $values[$field] = get($field);
      }

    }

    return $values;

  }

  /**
   * Extract the rule set from the field definition.
   *
   * @return  array
   */
  public function rules() {

    $rules = array();
    foreach ($this->fields as $field => $definition) {
      if (!empty($definition['rules'])) {
        $rules[$field] = $definition['rules'];
      }
    }

    return $rules;

  }

  /**
   * Get a list of messages.
   *
   * @return  Brick|string
   */
  public function messages() {

    if ($this->messages->count() > 0) {

      $ul = new Brick('ul', array('class' => 'message-list'));

      foreach ($this->messages->all('<li class="message message--type-{key}">{message}</li>') as $message) {
        $ul->append($message);
      }

      return $ul;

    }

    return '';

  }

  /**
   * Determine if the user provides valid values for all fields.
   *
   * @return  boolean
   */
  public function validate() {

    $rules  = $this->rules();
    $values = $this->data;

    $validator = new Validator($values, $rules);
    if ($validator->fails()) {
      $this->messages->merge( $validator->errors() );
      return false;
    }

    return true;

  }

  /**
   * Render the comment form. Alias for Form::__toString().
   *
   * @return  string
   */
  public function toString() {
    return (string)$this;
  }

  /**
   * Render the comment form. Performs field validation and adds alert messages
   * when errors occur.
   *
   * @return  string
   */
  public function __toString() {

    $config = plugin('comments')->config();

    // Validate all field values, if the form has been submitted. Protect the
    // form against malicious Cross-Site Forgery requests. Expects a random
    // token to match the value of a variable in the user’s current session.
    if(get('token') && csfr(get('token'))) {
      $this->trigger('submit');
    }

    // Render message list
    if ($list = $this->messages()) {
      $this->append($list);
    }

    // Honeypot protection via a textfield (which should be hidden using css)
    if ('css' === $config->get('honeypot')) {

      $label = l('comments.field.honeypot', 'Leave this field empty');
      $label = $config->get('honeypot.label', $label);
      $name  = $config->get('honeypot.name', 'url');
      $class = $config->get('honeypot.css', 'input input--type-text input--name-url');

      $this->field($name, array(
        'type'         => 'text',
        'label'        => $label,
        'size'         => 30,
        'class'        => $class,
        'autocomplete' => 'off',
      ));

    }

    // Require a minimum amount of time to be elapsed between the rendering of
    // the form and its submission
    if ($config->get('requiredReadingTime') > 0) {

      $this->append(array(
        'type'  => 'hidden',
        'name'  => 'tictoc',
        'value' => time(),
      ));

    }

    // Render all form fields
    foreach($this->fields as $field => $definition) {
      $this->build($definition);
    }

    // Form actions
    $group  = new Brick('div', array( 'class' => 'form-actions' ));
    $button = new Brick('input', array(
      'type'  => 'submit',
      'name'  => 'submit',
      'class' => 'btn btn--primary js-submit',
      'value' => l('comments.button.send', 'Send Comment')
    ));

    // Cross-Site Request Forgery protection
    $csfr = new Brick('input', array(
      'type'  => 'hidden',
      'name'  => 'token',
      'value' => csfr(),
    ));

    // Add elements to the form
    $this->append($button, $group);
    $this->append($csfr);

    // Disable client side validation while debugging
    if (c::get('debug')) {
      $this->attr('novalidate', 'novalidate');
    }

    // Convert to html string
    $this->attr['class'] = implode(' ', $this->classNames());
    return html::tag('form', $this->html(), $this->attr());

  }

  /**
   * Converts a field definition to a valid form field.
   *
   * @param   array  $field  Field definition.
   * @return  Brick
   */
  protected function build(array $field) {

    $container = $this;
    $name      = a::get($field, 'name');
    $type      = a::get($field, 'type', 'text');
    $required  = a::get($field, 'required', false);
    $default   = a::get($field, 'default', '');
    $value     = a::get($this->data, $name, $default);

    // Append an asterisk to required fields
    if ($required) {

      $field['label'] .= '<abbr title="' . l('comments.field.required', 'Required') . '">*</abbr>';
      $field['aria-required'] = true;

    }

    // Create a field label
    if (!empty($field['label'])) {

      $class = 'field field--type-' . $type . ' field--name-' . strtr($name, '_', '-');
      $container = new Brick('div', array('class' => $class));
      $label = new Brick('label', $field['label']);

      if (!empty($field['id'])) {
        $label->attr('for', $field['id']);
      }

      $container->append($label);

    }

    // Apply default class name
    if (empty($field['class'])) {
      $field['class'] = 'input input--' . $type . ' input--' . strtr($name, '_', '-');
    }

    // Create a whitelist of allowed attribute names
    $attrs = array(
      'id',
      'class',
      'name',
      'value',
      'required',
      'placeholder',
      'aria-required',
      'autocomplete'
    );

    switch ($type) {
      case 'text':
      case 'email':
      case 'url':
        $attrs[] = 'type';
        $attrs[] = 'size';
        break;
      case 'textarea':
        $attrs[] = 'rows';
        $attrs[] = 'cols';
        break;
    }

    $tag = ( $type === 'textarea' ) ? 'textarea' : 'input';
    $attrs = array_intersect_key($field, array_flip($attrs));

    // Create form field
    $element = new Brick($tag, '', $attrs);
    if ( !empty($value) && ('input' === $element->tag()) ) {
      $element->attr('value', $value);
    }

    // Render field and container
    if ($container != $this) {
      $this->append($element, $container);
    } else {
      $this->append($element);
    }

  }

}
