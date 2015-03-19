<?php

namespace Comments;

use A;
use Obj;
use Str;

/**
 * Finder
 *
 * A helper class to retrieve the fully qualified path for different plugin
 * resources.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Finder extends Obj {

  /**
   * Constructor.
   *
   * Create a new object instance maintining plugin paths.
   *
   * @param  string  $index  Base directory.
   */
  public function __construct($index) {

    // Determine plugin base directory
    $this->index = $index;
    $this->cache = dirname(dirname($index)) . DS . 'cache' . DS . basename($index);

    // Plugin root directories
    $this->library     = $index . DS . 'library';
    $this->extensions  = $index . DS . 'extensions';
    $this->models      = $index . DS . 'models';
    $this->views       = $index . DS . 'views';
    $this->controllers = $index . DS . 'controllers';
    $this->resources   = $index . DS . 'resources';

    // Plugin resources
    $this->languages   = $this->resources . DS . 'languages';
    $this->snippets    = $this->resources . DS . 'snippets';
    $this->assets      = $this->resources . DS . 'assets';

    // Plugin assets
    $this->css         = $this->assets . DS . 'css';
    $this->js          = $this->assets . DS . 'js';

  }

  /**
   * Load the contents of a given file.
   *
   * @param   string  $name  File name to load.
   * @param   string  $dir   Base directory to load the file from.
   *
   * @return  mixed
   */
  public function load($name, $dir = 'index') {

    $root = $this->get($dir);

    if (!$root || !file_exists($file = $root . DS . $name . '.php')) {
      return false;
    } else {
      return include_once($file);
    }

  }

  /**
   * Locate a template file. Searches for files in the site folder first. Takes
   * a file in the plugin folder as fallback.
   *
   * @param   string   $file  File to locate.
   * @param   boolean  $load  Whether to load the file.
   *
   * @return  string|boolean
   */
  public function locate($file, $load = false) {

    // Try to load the file from the site folder.
    $base = basename($this->index);
    $path = kirby()->roots()->snippets() . DS . $base . DS . $file . '.php';
    if (file_exists($path)) {
      if ($load) require($path);
      return $path;
    }

    // Try to load the file from the plugin’s resource folder
    $path = $this->snippets() . DS . $file . '.php';
    if (file_exists($path)) {
      if ($load) require($path);
      return $path;
    }

    // File not found
    return false;

  }

}
