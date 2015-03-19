<?php

namespace Comments\Support;

use A;
use Kirby;
use Error;
use Remote;
use Server;
use Visitor;

use Comment;

/**
 * Akismet API
 *
 * A helper class to communicate with the Akismet API (by Automattic).
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Support
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Akismet {

  /**
   * Scheme of the API url.
   */
  const API_SCHEME = 'http://';

  /**
   * Url of the Akismet API endpoint.
   *
   * @var string
   */
  const API_URL = 'rest.akismet.com';

  /**
   * Version of the Akismet API applied.
   *
   * @var  string
   */
  const API_VERSION = '1.1';

  /**
   * A string representing the Akismet API key (required).
   *
   * @var  string
   */
  private $key;

  /**
   * Url of the site performing the requests (required).
   *
   * @var  string
   */
  private $blog;

  /**
   * User agent to send with each request (required).
   *
   * @var  string
   */
  private $agent;

  /**
   * Throw exception on failure.
   *
   * @var  boolean
   */
  private $fail = false;

  /**
   * Constructor.
   *
   * Create a new instance of the Akismet API wrapper class.
   *
   * @param  string  $key  Akismet API key
   * @param  string  $url  Url of the site performing the request.
   */
  public function __construct($key, $url) {

    $this->key($key);
    $this->url($url);

  }

  /**
   * Get or set the API-key to use for the request.
   *
   * @param   string  $key  Akismet API key to use.
   * @return  string|self
   */
  public function key($key = null) {

    if (is_null($key)) {
      return $this->key;
    }

    $this->key = $key;
    return $this;

  }

  /**
   * Get or set the user agent send with each request.
   *
   * @param   string  $name  User agent to send.
   * @return  string|self
   */
  public function agent($name = null) {

    if (is_null($name)) {

      $kirby = 'Kirby/' . kirby::version();
      $plugin = isset($this->agent) ? $this->agent : 'Kirby Comments/' . plugin('comments')->version();

      return $kirby . ' | ' . $plugin;

    }

    $this->agent = $name;
    return $this;

  }

  /**
   * Control whether request errors should throw an exception or not.
   *
   * @param   boolean  $flag
   * @return  self
   */
  public function fail($flag = true) {

    $this->fail = $flag;
    return $this;

  }

  /**
   * Get or set the url of the site performing the request.
   *
   * @param   string  $url  Url of the site performing the request.
   * @return  string|self
   */
  public function url($url = null) {

    if (is_null($url)) {
      return $this->blog;
    }

    $this->blog = $url;
    return $this;

  }

  /**
   * Generate the url for the given API endpoint.
   *
   * @param   string  $name  Name of the endpoint.
   * @return  string
   */
  public function endpoint($name) {

    $subdomain = '';
    if (!in_array($name, array('verify-key'))) {
      $subdomain = $this->key() . '.';
    }

    return self::API_SCHEME . $subdomain . self::API_URL . '/' . self::API_VERSION . '/' . $name;

  }

  /**
   * Verify that the given API Key is valid.
   *
   * @param   string  $key  Akismet API key to use.
   * @return  boolean
   */
  public function verifyKey($key = null) {

    $key = is_null($key) ? $this->key() : $key;
    $url = $this->endpoint('verify-key');

    $response = $this->request($url, array(
      'key' => $key
    ));

    if ($response instanceof Error) {
      return false;
    }

    return 'valid' === $response->content;

  }

  /**
   * Perform an API request to determine whether the given comment contains
   * spam or not.
   *
   * @param   array    $content     Comment contents to send with the check.
   * @param   string   $userIp      IP address of the comment submitter.
   * @param   string   $userAgent   User agent string of the web browser submitting the comment.
   * @param   boolean  $discard     Indicates whether it is safe to discard the comment directly.
   *
   * @return  boolean
   */
  public function isSpam($content = array(), $userIp = null, $userAgent = null, &$discard = false) {

    $url = $this->endpoint('comment-check');

    if ($content instanceof Comment) {
      $content = $this->convertComment($content);
    }

    $content = $this->prepareContent($content, $userIp, $userAgent);
    $response = $this->request($url, $content);

    if ($response instanceof Error) {
      return false;
    }

    $discard = isset($response->headers['X-akismet-pro-tip']) && ('discard' === $response->headers['X-akismet-pro-tip']);
    return filter_var($response->content, FILTER_VALIDATE_BOOLEAN);

  }

  /**
   * Notify Akismet about false positives - items that were incorrectly
   * classified as spam.
   *
   * @param   array    $content     Comment contents to send
   * @param   string   $userIp      IP address of the comment submitter.
   * @param   string   $userAgent   User agent string of the web browser submitting the comment.
   *
   * @return  boolean
   */
  public function submitHam($content = array(), $userIp = null, $userAgent = null) {

    $url = $this->endpoint('submit-ham');

    if ($content instanceof Comment) {
      $content = $this->convertComment($content);
    }

    return $this->sendFeedback($url, $content, $userIp, $userAgent);

  }

  /**
   * Submit comments that weren't marked as spam but should have been.
   *
   * @param   array    $content     Comment contents to send.
   * @param   string   $userIp      IP address of the comment submitter.
   * @param   string   $userAgent   User agent string of the web browser submitting the comment.
   *
   * @return  boolean
   */
  public function submitSpam($content = array(), $userIp = null, $userAgent = null) {

    $url = $this->endpoint('submit-spam');

    if ($content instanceof Comment) {
      $content = $this->convertComment($content);
    }

    return $this->sendFeedback($url, $content, $userIp, $userAgent);

  }

  /**
   * Convert a comment instance to the expected data format.
   *
   * @param   Comment  $comment  Comment to convert.
   * @return  array
   */
  protected function convertComment(Comment $comment) {

    $content = $comment->toArray();

    $defaults = array(
      'comment_type' => 'comment',
      'referrer' => visitor::referrer(),
      'blog_lang' => site()->multilang() ? site()->language() : 'en',
      'blog_charset' => c::get('charset', 'utf8'),
    );

    $map = array(
      'text' => 'comment_content',
      'author_email' => 'comment_author_email',
      'author_url' => 'comment_author_url',
      'author_ip' => 'user_ip',
      'author_agent' => 'user_agent',
      'page_uri' => 'permalink',
    );

    // Swap array keys with the keys expected by Akismet
    foreach($content as $key => $value) {
      unset($content[$key]);
      if (isset($map[$key])) {
        $new = $map[$key];
        $content[$new] = $value;
      }
    }

    // Include the default values in the request
    $content = array_merge($defaults, $content);

    // Post-process a few of the raw values
    if (!empty($content['permalink'])) {
      $content['permalink'] = url($content['permalink']);
    }
    if (!empty($content['comment_content'])) {
      $content['comment_content'] = markdown($content['comment_content']);
    }

    return $content;

  }

  /**
   * Prepare the request data send to the Akismet API
   *
   * @param   array    $content     Comment contents to send.
   * @param   string   $userIp      IP address of the comment submitter.
   * @param   string   $userAgent   User agent string of the web browser submitting the comment.
   *
   * @return  array
   */
  protected function prepareContent($content = array(), $userIp = null, $userAgent = null) {

    if (empty($content['comment_type'])) {
      $content['comment_type'] = 'comment';
    }

    if (is_null($userIp)) {
      $content['user_ip'] = visitor::ip();
    } else if (!empty($userIp)) {
      $content['user_ip'] = $userIp;
    }

    if (is_null($userAgent)) {
      $content['user_agent'] = visitor::ua();
    } else if (!empty($userAgent)) {
      $content['user_agent'] = $userAgent;
    }

    return $content;

  }

  /**
   * Perform an API request at the Akismet service.
   *
   * @param   string  $url     API endpoint url.
   * @param   array   $data    Parameters to send with the request.
   *
   * @return  RemoteResponse|Error
   */
  protected function request($url, $data = array()) {

    $defaults = array(
      'blog' => $this->blog,
    );

    $response = remote::post($url, array(
      'agent'   => $this->agent(),
      'timeout' => 60,
      'data'    => array_merge($defaults, $data),
    ));

    $error = null;

    if ($response->code !== 200) {
      $msg = a::get($response->headers, 'X-akismet-debug-help', 'Invalid API request');
      $error = new Error('Akismet: ' . $msg);
    } else if ($response->error) {
      $error = new Error($response->message, $response->error);
    }

    if ($this->fail && !is_null($error)) {
      throw $error;
    }

    return is_null($error) ? $response : $error;

  }

  /**
   * Send feedback to the Akismet API about the classification of a comment.
   *
   * @param   string   $url         Url of the API endpoint to notify.
   * @param   array    $content     Comment contents to send.
   * @param   string   $userIp      IP address of the comment submitter.
   * @param   string   $userAgent   User agent string of the web browser submitting the comment.
   *
   * @return  boolean
   */
  protected function sendFeedback($url, $content = array(), $userIp = null, $userAgent = null) {

    $content = $this->prepareContent($content, $userIp, $userAgent);
    $response = $this->request($url, $content);

    if ($response instanceof Error) {
      return false;
    }

    return 'Thanks for making the web a better place.' === $response->content;

  }

}
