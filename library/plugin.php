<?php

namespace Comments;

use L;
use Error;
use Kirby;
use Router;
use Closure;
use Database;
use Response;
use Cache\Driver\File as FileCache;

use Comments\Config;
use Comments\Container;
use Comments\Roots;
use Comments\Database\Table;
use Comments\Database\ModelAbstract as Model;

/**
 * Comment Plugin
 *
 * This class represents the core of the plugin. It loads all plugin
 * dependencies and provides helpful methods to retrieve plugin options and
 * properties.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class CommentPlugin {

  /**
   * Name of the plugin.
   *
   * @var  string
   */
  protected $name;

  /**
   * Version of the plugin.
   *
   * @var  string
   */
  protected $version;

  /**
   * Reference to the kirby core.
   *
   * @var  Kirby
   */
  protected $kirby;

  /**
   * Reference to the current site object.
   *
   * @var  Site
   */
  protected $site;

  /**
   * Custom router to react to plugin specific actions.
   *
   * @var  Comments\Router
   */
  protected $router;

  /**
   * A service container maintaining all plugin components.
   *
   * @var  Comments\Container
   */
  protected $services;

  /**
   * Constructor.
   *
   * Create a new instance of the comment plugin.
   *
   * @param  Kirby  $kirby
   */
  public function __construct($kirby) {

    if (!($kirby instanceof Kirby)) {
      throw new Error('Plugin requires the kirby core');
    }

    $this->kirby   = $kirby;
    $this->site    = $kirby->site();

    $this->name    = 'comments';
    $this->version = '2.x-1.0-beta';

    $this->load();
    $this->boot();
    $this->localize();

  }

  /**
   * Load plugin dependencies.
   */
  protected function load() {

    $index   = $this->kirby->roots()->plugins() . DS . $this->name();
    $models  = $index . DS . 'models';
    $library = $index . DS . 'library';

    require_once($index . DS . 'helpers.php');
    require_once($index . DS . 'extensions' . DS . 'methods.php');
    require_once($index . DS . 'extensions' . DS . 'validators.php');

    // Comments library
    load(array(

      'comment'                            => $models . DS . 'comment.php',
      'comments'                           => $models . DS . 'comments.php',

      'comments\\config'                   => $library . DS . 'config.php',
      'comments\\finder'                   => $library . DS . 'finder.php',
      'comments\\container'                => $library . DS . 'container.php',
      'comments\\controller'               => $library . DS . 'controller.php',

      'comments\\view\\view'               => $library . DS . 'view' . DS . 'view.php',
      'comments\\view\\form'               => $library . DS . 'view' . DS . 'form.php',
      'comments\\view\\walker'             => $library . DS . 'view' . DS . 'walker.php',
      'comments\\view\\wizard'             => $library . DS . 'view' . DS . 'wizard.php',

      'comments\\import\\importer'         => $library . DS . 'import' . DS . 'importer.php',
      'comments\\import\\csvimporter'      => $library . DS . 'import' . DS . 'csv.php',

      'comments\\support\\str'             => $library . DS . 'support' . DS . 'str.php',
      'comments\\support\\akismet'         => $library . DS . 'support' . DS . 'akismet.php',
      'comments\\support\\session'         => $library . DS . 'support' . DS . 'session.php',
      'comments\\support\\messages'        => $library . DS . 'support' . DS . 'messages.php',
      'comments\\support\\validator'       => $library . DS . 'support' . DS . 'validator.php',

      'comments\\database\\modelabstract'  => $library . DS . 'database' . DS . 'model.php',

    ));

  }

  /**
   * Register plugin components as service.
   */
  protected function boot() {

    $c = $this->services = new Container();

    // Register plugin and kirby core as service parameter
    $this->share('name', $this->name());
    $this->share('version', $this->version());
    $this->share('root', $this->kirby->roots()->plugins() . DS . $this->name());

    // Register the path finder as service
    $this->share('finder', function($c) {
      return new Finder($c->get('root'));
    });

    // Register configuration store as service
    $this->share('config', function($c) {
      return new Config($c->get('root'), $c->get('name'));
    });

    // Register file cache as service
    $this->share('cache', function($c) {
      return new FileCache($c->finder()->cache());
    });

    // Register database connection as service
    $this->share('db', function($c) {
      $connections = $c->config()->get('database.connections');
      $default = $c->config()->get('database.default', 'sqlite');
      $credentials = array_merge($connections[$default], array('type' => $default));
      return new Database($credentials);
    });

    // Dynamically resolve database connections for models once a connection
    // is requested
    Model::resolver(function() use ($c) { return $c->get('db'); });

  }

  /**
   * Get the name of the plugin.
   *
   * @return  string
   */
  public function name() {
    return $this->name;
  }

  /**
   * Get the version of the plugin.
   *
   * @return  string
   */
  public function version() {
    return $this->version;
  }

  /**
   * Get access to the plugin’s service container.
   *
   * @return  Comments\Container
   */
  public function services() {
    return $this->services;
  }

  /**
   * Registers a factory method in the plugin’s service container.
   *
   * @param   string   $name      Name of the service to register.
   * @param   mixed    $service   Service description.
   *
   * @return  static
   */
  public function share($name, $service) {

    if ($service instanceof Closure) {
      $this->services->singleton($name, $service);
    } else {
      $this->services->param($name, $service);
    }

    return $this;

  }

  /**
   * Get access to the plugin router that allows to react to specifc URL
   * schemes to run custom actions.
   *
   * @return  Router
   */
  public function router() {

    if (is_null($this->router)) {

      $this->router = new Router($this->routes());
      $this->filters();

    }

    return $this->router;

  }

  /**
   * Get plugin routes and associated actions.
   *
   * @return  array
   */
  public function routes() {
    return $this->finder()->load('routes');
  }

  /**
   * Get the currently active route.
   *
   * @return  array
   */
  public function route() {
    return $this->router()->route();
  }

  /**
   * Add custom route filter. Allows to run a specific route only if certain
   * conditions are fullfilled.
   *
   * @return Closure[]
   */
  public function filters() {

    // Setup route filters if not already done
    if ( 0 === count( $this->router->filters() ) ) {

      // Add all plugin filters
      foreach ($this->finder()->load('filters') as $name => $callback) {
        $this->router->filter($name, $callback);
      }

    }

    return $this->router->filters();

  }

  /**
   * Load language files if available.
   */
  protected function localize() {

    $finder = $this->finder();
    $config = $this->config();

    $base    = 'en';
    $root    = $finder->languages();
    $current = $this->site->multilang() ? $this->site->language() : $config->get('language', $base);

    if (file_exists($root . DS . $base .'.php')) {

      $localization = $finder->load($base, 'languages');;
      $localization = $localization['data'];

      if ( ($current !== 'en') && file_exists($root . DS . $current . '.php') ) {
        $translation  = $finder->load($current, 'languages');
        $localization = array_merge($localization, $translation['data']);
      }

      l::$data = array_merge(l::$data, $localization);

    }

  }

  /**
   * Launch the comment system and all its sub-components.
   */
  public function launch() {

    // Start plugin router and retrieve controller response
    if( $route = $this->router()->run() ) {
      $response = $this->action( $route->action(), $route->arguments() );
    }

    // A filter has caused a route to fail. We return the error page.
    else if ( null === $route && $this->router()->route() ) {

      // @todo: A 404 response is not really helpful. We should provide more
      // accurate feedback to the user about what has caused the route to
      // fail.
      $response = $this->site->errorPage();

    }

    // Render the response value
    if (!empty($response)) {

      ob_start();
      echo $response;
      ob_end_flush();
      exit();

    }

    // Otherwise do nothing and let Kirby handle the current route.

  }

  /**
   * Execute a given controller action.
   *
   * @param   mixed  $action  Controller action to execute.
   * @param   array  $params  Parameters passed to the action method.
   *
   * @return  Response
   */
  public function action($action, $params = array()) {

    // Callable action given
    if (is_callable($action)) {
      $action($params);
    }

    // Handle: Only controller given. Action missing
    if (!is_string($action) || strpos($action, '::') === false) {
      throw new Error('Invalid action');
    }

    // Separated controller from action to perform
    list($controller, $action) = explode('::', $action, 2);

    // Construct the expected folder path for controller
    $filename = strtolower(str_replace('Controller', '', $controller));
    $filepath = $this->finder()->controllers() . DS . $filename . '.php';
    if (!file_exists($filepath)) {
      throw new Error("Controller [$controller] does not exist");
    }

    // Should be save to load the file now
    require_once($filepath);

    // Ensure the expected class is loaded now
    $class = 'Comments\\' . $controller;

    if (!class_exists($class)) {
      throw new Error("Missing a valid class definition in the controller file [$filepath]");
    }

    // Create a new controller instance
    $instance = new $class();

    // Dynamically inject plugin core as service hub
    if (method_exists($instance, 'hub')) {
      $instance->hub($this);
    }

    // Perform action. Try a fallback. Otherwise fail.
    $fallback = 'missing';

    if (method_exists($instance, $action)) {
      $response = call(array($instance, $action), $params);
    } else if (method_exists($instance, $fallback)) {
      $response = call(array($instance, $fallback), $params);
    } else {
      throw new Error("Controller action [$action] not implemented");
    }

    // Ensure we retrieve a response object
    if (!is_a($response, 'Response')) {
      $response = new Response($response);
    }

    return $response;

  }

  /**
   * Display name and version of the plugin.
   *
   * @return  string
   */
  public function toString() {
    return (string)$this;
  }

  /**
   * Display name and version of the plugin.
   *
   * @return string
   */
  public function __toString() {
    return $this->name() . ' v' . $this->version();
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
    return $this->services->get($method);
  }

}
