<?php

namespace Comments\Import;

/**
 * CSV Comment Importer
 *
 * A helper class to import comments from an CSV file. Uses fopen to reduce the
 * overall memory load for huge import files.
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Import
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class CSVImporter extends Importer {

  /**
   * File handle.
   *
   * @var  Stream
   */
  protected $handle;

  /**
   * Constructor.
   *
   * Creates a new csv importer instance.
   *
   * @param  Database  $connection  Database connection to use.
   * @param  string    $table       Database table to import to.
   * @param  array     $options     Import options.
   */
  public function __construct($connection, $table, $options = array()) {
    parent::__construct($connection, $table, $options);
  }

  /**
   * Retrieve default values for the import progress.
   *
   * @return  array
   */
  public function defaults() {

    return array_merge(parent::defaults(), array(
      'delimiter' => ';',
      'enclosure' => '"',
    ));

  }

  /**
   * Allow the importer to create a file handle.
   *
   * @param   string  $file  File to load.
   * @return  boolean        Whether the file was loaded successfully.
   */
  protected function load($file) {

    if (!($this->handle  = fopen($file, 'r'))) {
      throw new Error("Could not read file [$file]");
    }

    return ($this->handle !== false);

  }

  /**
   * Read the next number of rows from the the file handle.
   *
   * @param   integer  $count  Number of items to retireve next.
   * @return  array            Collection of items retrieved from the file.
   */
  protected function next($items = 1) {

    $buffer = array();
    $num = 0;

    while ($num < $items && !feof($this->handle)) {
      $buffer[] = fgets($this->handle);
      $num++;
    }

    return !empty($buffer) ? $buffer : false;

  }

  /**
   * Extract the actual data values from a row.
   *
   * @param   string  $str  Data row retrieved from the file.
   * @return  array         Extracted data values.
   */
  protected function row($str) {
    return str_getcsv($str, $this->delimiter(), $this->enclosure());
  }

  /**
   * Clean up all file handles.
   *
   * @return  boolean  Status of the cleaning operation.
   */
  protected function finish() {
    return fclose($this->handle);
  }

}
