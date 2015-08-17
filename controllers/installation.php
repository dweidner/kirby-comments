<?php

namespace Comments;

use C;
use F;
use User;
use Upload;
use Folder;
use Redirect;
use Database;

use Comments\Finder;
use Comments\Config;
use Comments\View\View;
use Comments\View\Wizard;
use Comments\Database\Table;
use Comments\Import\CSVImporter;

/**
 * Installation Controller
 *
 * Guides a user through the installation process of the plugin. Controls when
 * to render which view and layout, creates the required databases and installs
 * available panel integrations.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class InstallationController extends Controller {

  /**
   * Show the installation page.
   *
   * @param   integer   $step  Progress of the installation.
   * @return  Response
   */
  public function index($progress = 1) {

    // Cache frequently used variables
    $user   = user::current();
    $access = $user && $user->isAdmin();
    $root   = $this->hub()->finder()->views() . DS . 'installation';
    $wizard = new Wizard($root);

    // Force login before continuing with installation wizard
    if (!$access && $progress > 1) {
      $this->redirect($wizard->url(1));
    }

    // Skip login if already signed in as admin
    else if ($access && $progress == 1) {
      $this->redirect($wizard->url($progress + 1));
    }

    // Step 1: Login
    $wizard->add(array(
      'title'    => 'Step 1',
      'desc'     => 'Authentication',
      'required' => true,
      'rules'    => array(
        'username' => 'required|user',
        'password' => 'required',
      ),
    ));

    // Step 2 (Required)
    $default = $this->hub()->config()->get('database.default');
    $connections = $this->hub()->config()->get('database.connections');

    $wizard->add(array(
      'title'      => 'Step 2',
      'desc'       => 'Database Connection',
      'required'   => true,
      'connection' => $connections[$default],
    ));

    // Step 3 (Required)
    $wizard->add(array(
      'title'    => 'Step 3',
      'desc'     => 'Database Tables',
      'required' => true,
      'tables'   => array( c::get('db.prefix', '') . 'comments' ),
    ));

    // Step 4 (Optional)
    $wizard->add(array(
      'title'    => 'Step 4',
      'desc'     => 'Import',
      'required' => false,
      'rules'    => array(
        'head' => 'required',
        'delimiter' => 'required|max:1',
        'enclosure' => 'required|max:1',
      ),
      'columns' => array(
        'id',
        'status',
        'page_uri',
        'created_at',
        'updated_at',
        'text',
        'author',
        'author_email',
        'author_url',
        'author_ip',
        'author_agent',
        'username',
        'rating',
        'parent_id',
      ),
    ));

    // Step 5 (Optional)
    $wizard->add(array(
      'title'    => 'Step 5',
      'desc'     => 'Comments Field',
      'required' => false,
    ));

    // Include partials
    $wizard->nest('header', $root . DS . 'header.php');
    $wizard->nest('nav', $root . DS . 'nav.php')->with(array(
      'step'  => $progress,
      'items' => $wizard->queue(),
    ));

    // Register event handler
    $wizard->nth(1)->on('submit', array($this, 'login'));
    $wizard->nth(2)->on('submit', array($this, 'connect'));
    $wizard->nth(3)->on('submit', array($this, 'tables'));
    $wizard->nth(4)->on('submit', array($this, 'import'));
    $wizard->nth(5)->on('submit', array($this, 'installField'));

    // Execute the proper wizard step
    return $wizard->launch($progress);

  }

  /**
   * A wizard callback used to perform user authentication.
   *
   * @param   View   $view  Submitted view
   * @param   array  $form  Form data
   *
   * @return  boolean
   */
  public function login($view, $form) {

    $user = site()->user($form['username']);
    if ($user && $user->login($form['password'])) {
      return true;
    }

    $view->errors(array('username' => 'Invalid login attempt'));

  }

  /**
   * A wizard callback used to check the database connection.
   *
   * @param   View   $view  Submitted view
   * @param   array  $form  Form data
   *
   * @return  boolean
   */
  public function connect($view, $form) {

    if (is_null($this->hub()->db()->connection())) {
      $view->errors(array('database' => 'Could not connect to the given database'));
      return false;
    }

    return true;

  }

  /**
   * A wizard callback used to create all required database tables.
   *
   * @param   View   $view  Submitted view
   * @param   array  $form  Form data
   *
   * @return  boolean
   */
  public function tables($view, $form) {

    $table = c::get('db.prefix', '') . 'comments';
    $db = $this->hub()->db();

    // Drop an existing table
    $db->dropTable($table);

    // Create a new database table with the required columns
    $created = $db->createTable($table, array(
      'id'           => array( 'type' => 'id' ),
      'page_uri'     => array( 'type' => 'varchar', 'null' => false, 'key' => 'index' ),
      'status'       => array( 'type' => 'int', 'null' => false, 'default' => 0 ),
      'created_at'   => array( 'type' => 'timestamp', 'null' => false, 'key' => 'index' ),
      'updated_at'   => array( 'type' => 'timestamp', 'null' => false ),
      'text'         => array( 'type' => 'text', 'null' => false ),
      'author'       => array( 'type' => 'varchar', 'null' => false ),
      'author_email' => array( 'type' => 'varchar', 'null' => false, 'default' => '', 'key' => 'index' ),
      'author_url'   => array( 'type' => 'varchar', 'null' => false, 'default' => '' ),
      'author_ip'    => array( 'type' => 'varchar', 'null' => false, 'default' => '' ),
      'author_agent' => array( 'type' => 'varchar', 'null' => false, 'default' => '' ),
      'username'     => array( 'type' => 'varchar', 'null' => false, 'default' => '' ),
      'rating'       => array( 'type' => 'int', 'null' => false, 'default' => 0 ),
      'parent_id'    => array( 'type' => 'int', 'null' => false, 'default' => 0, 'key' => 'index' ),
    ));

    // Notify user about the failure
    if (!$created) {
      $view->errors(array('tables' => "Could not create database table [$table]"));
      return false;
    }

    // Continue with the installation wizard
    return true;

  }

  /**
   * A wizard callback used to import comments from a CSV file.
   *
   * @param   View   $view  Submitted view
   * @param   array  $form  Form data
   *
   * @return  boolean
   */
  public function import($view, $form) {

    // Try to upload the file to the cache
    $file = $this->hub()->finder()->cache() . DS . '{safeName}.{safeExtension}';
    $upload = new Upload($file);

    // Stop import process if error occured
    if ($upload->error()) {
      $view->errors(array('file' => $upload->error()->message()));
      return false;
    }

    // Prepare database connection
    $cols = explode(',', $form['head']);
    $cols = array_map('trim', $cols);

    // Start import process from file
    $import = new CSVImporter($this->hub()->db(), 'comments', array(
      'delimiter' => $form['delimiter'],
      'enclosure' => $form['enclosure'],
      'head'      => $cols,
    ));

    if (!$import->start($upload->file()->root())) {
      $view->errors(array('import' => 'Unknown error occured during the import'));
      return false;
    }

    return true;

  }

  /**
   * A wizard callback used to install the custom field.
   *
   * @param   View   $view  Submitted view
   * @param   array  $form  Form data
   *
   * @return  boolean
   */
  public function installField($view, $form) {

    $path = kirby()->roots()->fields() . DS . 'comments';
    $target = new Folder($path);

    if ($target->exists()) {
      $view->errors(array('field' => 'A field with the same name already exists.'));
      return false;
    }

    $path = $this->hub()->finder()->resources() . DS . 'fields' . DS . 'comments';
    $source = new Folder($path);

    if (!$source->copy($target->root())) {
      $view->errors(array('field' => 'Could not copy custom field to target directory. Is the `site` folder writeable?'));
      return false;
    }

    return $this->redirect('home');

  }

  /**
   * Generate the required wizard assets.
   *
   * @param   string  $file  Requested asset file.
   * @return  boolean
   */
  public function assets($file) {

    $root = $this->hub()->finder()->assets();
    $path = $root . DS . $file;

    if ('css/styles.css' === $file) {
      $this->styles();
    } else if ('js/scripts.css' === $file) {
      $this->scripts();
    } else if (file_exists($path) && 'woff' === f::extension($path)) {
      header('Content-type: application/font-woff');
      echo f::read($path);
      exit;
    }

    return false;

  }

  /**
   * Combine and cache all stylesheets.
   */
  public function styles() {

    $file = $this->hub()->finder()->cache() . DS . 'wizard.css';

    if (!file_exists($file)) {
      $css = $this->combine(array('css/font-awesome.css', 'css/panel.css', 'css/hacks.css' ));
      $css = str_replace('{{url}}', url('plugins/comments/assets'), $css);
      f::write($file, $css);
    }

    header('Content-type: text/css');
    echo f::read($file);
    exit;

  }

  /**
   * Combine and cache all javascript files.
   */
  public function scripts() {

    $file = $this->hub()->finder()->cache() . DS . 'wizard.js';

    if (!file_exists($file)) {
      $js = $this->combine(array('js/jquery.js', 'js/jquery.breadcrumb.js', 'js/jquery.sidebar.js' ));
      f::write($file, $js);
    }

    header('Content-type: text/javascript');
    echo f::read($file);
    exit;

  }

  /**
   * Combines the contents of all files (of a certain type).
   *
   * @param   string  $files     Files to combine.
   * @return  string
   */
  protected function combine($files) {

    $root   = $this->hub()->finder()->assets();
    $output = '';

    foreach ($files as $file) {
      $file = $root . DS . $file;
      $output .= f::read($file);
    }

    return $output;

  }

}
