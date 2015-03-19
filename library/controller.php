<?php

namespace Comments;

use R;
use Obj;
use Str;
use Server;
use Redirect;
use Response;

use Comments\Support\Session;
use Comments\Support\Messages;

/**
 * Comment Controller
 *
 * In kirby a controller acts as an agent between a model and its presentation
 * in a view. It controls a model’s state and triggers certain actions.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
abstract class Controller extends Obj {

  /**
   * Reference to the plugin core maintaining all plugin services.
   *
   * @var  Comments\CommentPlugin
   */
  protected $hub;

  /**
   * Get/set the reference to the plugin core acting as a service hub.
   *
   * @param   Comments\CommentPlugin  $hub
   * @return  Comments\CommentPlugin
   */
  public function hub($hub = null) {

    if (is_null($hub)) {
      return $this->hub;
    }

    $this->hub = $hub;
    return $this;

  }

  /**
   * Return a success response.
   *
   * @param   string  $msg   Optional message to send with the response.
   * @param   integer $code  Response code to send.
   * @param   array   $data  Data to return.
   *
   * @return  Response
   */
  protected function success(/* [ $msg = null, $code = 200, ] $data = array() */) {

    $args = func_get_args();

    switch (count($args)) {
      case 1:
        list($msg, $code, $data) = array(null, 200, reset($args));
        break;

      case 3:
        list($msg, $code, $data) = $args;
        break;

      default:
        list($msg, $code, $data) = array(null, 200, null);
        break;
    }

    return $this->response($msg, $code, $data);

  }

  /**
   * Return an error response.
   *
   * @param   string  $msg   Optional message to send with the response.
   * @param   integer $code  Response code to send.
   * @param   array   $data  Data to return.
   *
   * @return  Response
   */
  protected function error(/* [ $msg = null, $code = 400, ] $data = null */) {

    $args = func_get_args();

    switch (count($args)) {
      case 1:
        list($msg, $code, $data) = array(null, 400, reset($args));
        break;

      case 3:
        list($msg, $code, $data) = $args;
        break;

      default:
        list($msg, $code, $data) = array(null, 400, null);
        break;
    }

    return $this->response($msg, $code, $data);

  }

  /**
   * Generate the controller response.
   *
   * @param   string  $msg   Optional message to send with the response.
   * @param   integer $code  Response code to send.
   * @param   array   $data  Data to return.
   *
   * @return  Response
   */
  protected function response($msg, $code, $data) {

    if (!r::is('ajax')) {
      return $this->redirect('back', $data);
    }

    $response = array(
      'status'  => 'error',
      'data'    => $data,
      'code'    => $code,
      'message' => $msg
    );

    return response::json($response, $code);

  }

  /**
   * Redirect to a specific page.
   *
   * @param   string  $target   Page to redirect to.
   * @param   array   $data     Optional data to save in a users session.
   */
  protected function redirect($target, $data = null) {

    // Write optional session data
    if ($data instanceof Messages) {
      Session::flash('errors', $data->toArray());
    } else if (is_array($data)) {
      Session::flash($data);
    } else if (!is_null($data)) {
      Session::flash('data', $data);
    }

    // Allow to specify the redirect uri as parameter
    $url = r::get('redirect_to');

     if (!empty($url)) {
      redirect::to($url);
    }

    // Perform redirect
    switch ($target) {
      case 'home':
        redirect::home();
        break;
      case 'back':
        redirect::back();
        break;
      case '404':
        $page = site()->errorPage();
        redirect::to($page->uri());
        break;
      case 'referer':
        $referer = server::get('HTTP_REFERER');
        redirect::to($referer);
        break;
      default:
        redirect::to($target);
        break;
    }

  }

}
