<?php

namespace Comments\Import;

use A;

/**
 * Abstract Comment Importer
 *
 * A helper class to import comments from a file to the applications database.
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Import
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
abstract class Importer {

  /**
   * Database connection to use for the import.
   *
   * @var  Database
   */
  protected $connection;

  /**
   * Name of the database table to write to.
   *
   * @var  array
   */
  protected $table;

  /**
   * Collection of import options.
   *
   * @var  string
   */
  protected $options;

  /**
   * Constructor.
   *
   * Creates a new importer instance.
   *
   * @param  Database  $connection  Database connection to use.
   * @param  string    $table       Database table to import to.
   * @param  array     $options     Import options.
   */
  public function __construct($connection, $table, $options = array()) {

    $this->connection = $connection;
    $this->table = $table;
    $this->options = array_merge($this->defaults(), $options);

    $file = $this->option('file');

    if (!empty($file) && !is_readable($file)) {
      throw Exception('Invalid file name');
    }

  }

  /**
   * Get the database connection to use.
   *
   * @return \Database
   */
  protected function connection() {
    return $this->connection;
  }

  /**
   * Get the database table name to import to.
   *
   * @return  string
   */
  protected function table() {
    return $this->table;
  }

  /**
   * Retrieve default values for the import progress.
   *
   * @return  array
   */
  public function defaults() {

    return array(
      'file' => null,
      'head' => null,
      'itemsPerBatch' => 50,
    );

  }

  /**
   * Return the value of all import options.
   *
   * @return  array
   */
  public function options() {
    return $this->options;
  }

  /**
   * Get or set an option value.
   *
   * @param   string  $key    Name of the option to retrieve.
   * @param   mixed   $value  Value for the option
   * @return  mixed
   */
  public function option($key, $value = null) {

    if (is_null($value)) {
      return a::get($this->options, $key);
    }

    $this->options[$key] = $value;
    return $this;

  }

  /**
   * Start the import process. Load a given number of data rows into a buffer
   * and insert the parsed values into the database table.
   *
   * @param  string  $file  File to import
   */
  public function start($file = null) {

    // Fallback to the file given via the import options
    if (is_null($file)) {
      $file = $this->option('file');
    }

    // Ensure the file we want to read from is accessible
    if (empty($file) || !is_readable($file)) {
      return false;
    }

    // Determine the data columns contained in the file
    $header = a::get($this->options, 'head', array());
    $cols   = array_filter($header, function($v) { return !empty($v) && ('x' !== $v); });
    $ignore = array_keys($header, 'x');

    if (empty($cols)) {
      return false;
    }

    // Create a file handle
    if (!$this->load($file)) {
      return false;
    }

    // Initialize buffer
    $max   = $this->itemsPerBatch();
    $items = null;

    // Process the file unless we have reached its end
    while ($items !== false) {

      $num    = 0;
      $buffer = array();

      // Read lines into the buffer until we have collected enough items for the
      // current batch or we have reached the end of file ($items === false)
      while ($num < $max && ($items = $this->next($max - $num))) {

        $buffer = is_array($items) ? array_merge($buffer, $items) : $buffer;
        $num = count($buffer);

      }

      // Allow the importer to retrieve the actual values from the lines written
      // to the buffer. Ignore unneeded data values.
      for ($i = 0; $i < $num; $i++) {

        if ($buffer[$i]) {

          $data = $this->row($buffer[$i]);

          if (!empty($ignore) && is_array($data)) {
            foreach ($ignore as $key) unset($data[$key]);
          }

          $buffer[$i] = $data;

        }

      }

      // Insert the data into the given database table
      $this->insert($cols, $buffer);

    }

    return $this->finish();

  }

  /**
   * Allow the importer to create a file handle.
   *
   * @param   string  $file  File to load.
   * @return  boolean        Whether the file was loaded successfully.
   */
  abstract protected function load($file);

  /**
   * Instruct the importer to read the next number of rows from the file handle.
   *
   * @param   integer  $count  Number of items to retireve next.
   * @return  array            Collection of items retrieved from the file.
   */
  abstract protected function next($count = 1);

  /**
   * Allow the importer to extract the actual data values from a single data row.
   *
   * @param   string  $str  Data row retrieved from the file.
   * @return  array         Extracted data values.
   */
  abstract protected function row($str);

  /**
   * Allow the importer to clean up all resources used.
   *
   * @return  boolean  Status of the cleaning operation.
   */
  abstract protected function finish();

  /**
   * Insert the extracted data values to the database table.
   *
   * @param   array    $cols  Table columns to write the data to.
   * @param   array    $data  Values to insert.
   * @return  boolean
   */
  protected function insert($cols, $data) {

    $values = array();
    $db = $this->connection();

    // By default kirby does not support including multiple values per query.
    // Consquently we implement the escaping manually and hope the performance
    // gain is worth it.
    foreach ($data as $items) {

      // Ignore invalid values
      if (empty($items)) continue;

      // Prepare values of the current row
      $row = array();

      foreach($items as $item) {
        if(is_array($item)) {
          $row[] = "'" . $db->escape(json_encode($item)) . "'";
        } else {
          $row[] = "'" . $db->escape($item) . "'";
        }
      }

      $values[] = '(' . implode(', ', $row) . ')';

    }

    // Execute query
    $sql = sprintf('INSERT INTO %1$s (%2$s) VALUES %3$s;', $this->table, implode(', ', $cols), implode(', ', $values));
    $db->fail()->execute($sql);

  }

  /**
   * Handle dynamic method calls.
   *
   * @param  string  $method
   * @param  array   $arguments
   * @return mixed
   */
  public function __call($method, $arguments) {
    return isset($this->options[$method]) ? $this->options[$method] : null;
  }

}
