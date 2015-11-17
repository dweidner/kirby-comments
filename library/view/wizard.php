<?php

namespace Comments\View;

use F;
use R;
use Url;
use Error;
use Redirect;

use Comments\Support\Messages;
use Comments\Support\Validator;

/**
 * Wizard Dialog
 *
 * @todo Wizard class description
 *
 * @package     Kirby CMS
 * @subpackage  Comments\View
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Wizard extends View {

  /**
   * Base uri of the wizard.
   *
   * @var  string
   */
  protected $uri;

  /**
   * Collection of wizard sections.
   *
   * @var  array
   */
  protected $queue;

  /**
   * Constructor.
   *
   * Creates a new wizard instance.
   *
   * @param  string  $root  Base directory to load the views from.
   * @param  string  $uri   Base uri of the wizard.
   */
  public function __construct($root, $uri = null) {

    // Auto-detect wizard uri, if none is given
    if (is_null($uri)) {
      $this->uriFromPath();
    } else {
      $this->uri($uri);
    }

    // Initialize wizard
    $this->queue = array();
    parent::__construct($root . DS . 'index.php');

  }

  /**
   * Get or set the base uri applied by the wizard dialog.
   *
   * @param   string  $uri  Base uri to set.
   * @return  self
   */
  public function uri($uri = null) {

    if (is_null($uri)) {
      return $this->uri;
    }

    $this->uri = $path;
    return $this;

  }

  /**
   * Get the wizard base uri from the current path.
   *
   * @return  string
   */
  public function uriFromPath() {

    $path = kirby::instance()->path();

    // Check if the current path includes the progress indicator
    if (preg_match('#/\d$#', $path)) {

      // Strip numeric value indicating the progress
      $parts = explode('/', $path);
      array_pop($parts);
      $path = implode('/', $parts);

    }

    return $this->uri = $path;

  }

  /**
   * Generate the url for the current wizard instance or one of its child
   * sections.
   *
   * @param   integer  $step  Optional index of the step to retrieve the url for.
   * @return  string
   */
  public function url($step = null) {

    if (!is_numeric($step)) {
      return url($this->uri());
    }

    return url($this->uri() . '/' . $step);

  }

  /**
   * Create and add a new tab to the wizard dialog.
   *
   * @param  array  $step  Optional attributes
   */
  public function add($step = array()) {

    $index = count($this->queue) + 1;
    $base  = f::dirname($this->file);

    // Provide default values for each step
    $defaults = array(
      'title'    => sprintf( l('comments.wizard.title', 'Step %s'), $index ),
      'desc'     => '',
      'url'      => $this->url($index),
      'index'    => $index,
      'base'     => $base,
      'template' => 'steps' . DS . 'step-' . $index,
      'required' => true,
      'rules'    => array(),
      'wizard'   => $this,
    );

    // Create a new view with the given attributes
    $scope   = array_merge($defaults, $step);
    $partial = $base . DS . $scope['template'] . '.php';
    return $this->queue[] = $this->nest('content', $partial)->with($scope);

  }

  /**
   * Retrieve all wizard sections.
   *
   * @return  array
   */
  public function queue() {
    return $this->queue;
  }

  /**
   * Alias for Wizard::get().
   *
   * @param   integer  $index
   * @return  View
   */
  public function get($index) {
    return $this->nth($index);
  }

  /**
   * Get the wizard step at the given position of the queue.
   *
   * @param   integer  $index
   * @return  View
   */
  public function nth($index) {

    $index = min( max( intval($index) - 1, 0 ), count($this->queue) - 1 );
    return isset($this->queue[$index]) ? $this->queue[$index] : false;

  }

  /**
   * Run the wizard dialog.
   *
   * @param   integer  $index
   * @return  string
   */
  public function launch($index = 0) {

    // Retrieve active view
    if (!$view = $this->nth($index)) {
      return false;
    }

    // Trigger submit event
    if (get('token') && csfr(get('token'))) {

      $form = r::data();
      $validator = new Validator($form, $view->rules());
      $valid = $validator->passes();

      // Goto next wizard step or display validation errors
      if ($valid && $view->trigger('submit', compact('form'))) {
        $next = $view->index() + 1;
        redirect::to($this->url($next));
      } else if (!$valid) {
        $view->errors($validator->errors());
      }

    }

    // Generate view and return the contents
    return $this->with(array(
      'url' => $this->url(),
      'content' => $view->content(),
    ));

  }

  /**
   * Retrieve the html markup for an error message.
   *
   * @param   string  $message  Error message to render.
   * @return  string
   */
  public function alert($message = null, $format = null) {

    static $tpl = array(
      '<div class="message message-is-alert">',
        '<div class="message-content">{message}</div>',
        '<div class="message-toggle"><i>×</i></div>',
      '</div>'
    );

    if (is_null($format)) {
      $format = implode($tpl);
    }

    return $message instanceof Messages ?
              $message->toString($format) :
              str_replace('{message}', $message, $format);

  }

}
