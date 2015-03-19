<?php

/**
 * Plugin Configuration
 *
 * Contains the default plugin configuration. Users can override the settings
 * in the config file of the corresponding site (`site/config/config.php`) by
 * prefixing each option with the plugin name (`comments`).
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 *
 * @var         array
 */

return array(

  /**
   * Blacklist
   *
   * Sometimes it can be useful to simply block certain users or comment
   * contents from the site. You can do this by specifing either the author
   * name, the author email, the author ip, the author agent or the author url
   * that should be blocked. Another possibility is to define a list of words
   * which will cause the system to block comments containing those in the
   * comment text.
   *
   * Comments matching an item of you blacklist will be marked as spam.
   *
   * Example:
   * c::set('comments.blacklist', array(
   *   'Spambot',
   *   '80.104.0.105',
   *   'viagra',
   * ));
   *
   * @var array
   */
  'blacklist' => array(),

  /**
   * Markdown support in comments
   *
   * You can allow your users to use a limited set of html tags within their
   * comments. To disable markdown support entirely set this option to false.
   *
   * @var  array|boolean
   */
  'markdown' => array(
    'a',
    'p',
    'em',
    'strong',
    'ul',
    'ol',
    'li',
    'code',
    'pre',
    'blockquote'
  ),

  /**
   * Database
   *
   * Allows users to provide the plugin with database credentials. By default a
   * new sqlite database will be created in the site’s content directory.
   *
   * @var  array
   */
  'database' => array(

    /**
     * Default database connection used by the Comments plugin.
     *
     * @var  string
     */
    'default' => c::get('comments.driver', 'sqlite'),

    /**
     * Array of database credentials. You should provide credentials for the
     * connection you want to use for your comments.
     *
     * @var  array
     */
    'connections' => array(

      'sqlite' => array(
        'database' => kirby()->roots()->content() . DS . 'comments.sqlite',
        'prefix'   => '',
      ),

      'mysql' => array(
        'host'     => c::get('db.host', 'localhost'),
        'database' => c::get('db.name', ''),
        'prefix'   => c::get('db.prefix', ''),
        'user'     => c::get('db.user', 'root'),
        'password' => c::get('db.password', ''),
        'charset'  => c::get('charset', 'utf8'),
      ),

    ),

  ),

  /**
   * Capabilities
   *
   * Defines which user role is required to perform certain actions. Use the
   * keyword `all` to provide all visitors with access to corresponding action.
   * Note that in addition to the role specified for the `update` and `create`
   * capability the author of a comment will always be allowed to change or
   * remove his comments.
   *
   * @var  array
   */
  'capabilities' => array(

    /**
     * Write and publish a new comment (approval required).
     *
     * @var  string
     */
    'create' => 'all',

    /**
     * Read approved comments on the site.
     *
     * @var  string
     */
    'read'   => 'all',

    /**
     * Change the title and text of a comment. Note: The author of a comment is
     * always allowed to change its contents.
     *
     * @var  string
     */
    'update' => 'admin',

    /**
     * Delete a comment from the site. Note: The author of a comment is always
     * allowed to delete it.
     *
     * @var  string
     */
    'delete' => 'admin',

  ),

  /**
   * Honeypot Spam Prevention
   *
   * Everyone knows and hates CAPTCHAS. Honeypot is an unobtrusive alernative
   * preventing already most of the spambots from posting comments. The Honeypot
   * method takes advantage of a bots desire to fill most of a forms fields.
   * The technique simply adds an invisible field to the form. If that field
   * contains any data on submission it should be safe to assume that a machine
   * has filled the form.
   *
   * There are different variations of the technique. This plugin implements
   * two of them:
   *
   * 1. Add a textfield hidden via CSS. Ignore the submission if the form field
   *    is not empty.
   * 2. Add a hidden field via JavaScript. Ignore the submission if a value for
   *    the hidden field is missing (assumes that bots have JavaScript disabled).
   *
   * NOTE: Both techniques require to add some css/javascript to your theme which
   * can not be done automatically by a kirby plugin.
   *
   * @var  string|boolean  Expected values: css, js
   */
  'honeypot' => 'css',

  /**
   * Time-based Spam Prevention
   *
   * Another strategy applied to protect a site against spam messages is to
   * measure the time between the rendering of the page and the submission of
   * the form. A regular user needs a certain amount of time to skim the
   * contents of the article and fill the form. Is the time difference between
   * the rendering and the submission smaller than a certain threshold it should
   * be safe to assume a machine has filled the form (at least that is the
   * assumption).
   *
   * @var   integer  Time in seconds required
   */
  'requiredReadingTime' => 0,

  /**
   * Akismet Configuration
   *
   * Comment spam is a serious problem in the web. Akismet is a popular service
   * applied to fight comment spam (among others by WordPress). The service
   * classifies comments as spam using different heuristics (e.g. Bayesian
   * Filtering). You can create a free account for personal use at their
   * website. Once created specify the api key in your sites configuration
   * @link(http://akismet.com/plans/, Akismet Plans).
   *
   * @var  array
   */
  'akisment' => array(

    /**
     * Akismet Key
     *
     * The API key used to help Akismet identify the account the respective
     * requests belong to. If no API key is given, Akismet will not be used for
     * spam detection.
     *
     * Example:
     * c::set('comments.akismet.key', '123YourAPIKey');
     *
     * @var  string|boolean
     */
    'key' => false,

    /**
     * Akismet Strictness
     *
     * Akismet has a mechanism to identify `blatant spam` which can be safely
     * discarded without saving it in any queue. If you do not trust the
     * internals of Akismet and want to proofread the classification results
     * you can disable this behavior.
     *
     * Example:
     * c::set('comments.akismet.strictness', 'keep');
     *
     * @var  string  Allowed values are: keep/discard
     */
    'strictness' => 'discard',

  ),

);
