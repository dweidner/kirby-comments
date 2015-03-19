<?php

namespace Comments\Database;

use A;
use R;
use Field;
use Error;
use Closure;
use Collection;
use Database;
use Database\Query;

use Comments\Support\Str;
use Comments\Support\Messages;
use Comments\Support\Validator;

/**
 * Abstract Database Model
 *
 * This class implements the active record pattern and therefore provides an
 * abstraction of a single row in a given database table. As a result changes to
 * a model instance can be easily synchronized with their corresponding entries
 * in the table.
 *
 * This implementation is heavily based on the Eloquent database model of the
 * @link(http://www.laravel.com, Laravel framework) but adapted to work within
 * the Kirby environment.
 *
 * @see         http://en.wikipedia.org/wiki/Active_record_pattern
 * @see         http://laravel.com/docs/eloquent
 *
 * @method  Query    select(mixed $columns)  Set columns to select.
 * @method  Query    values(mixed $values)   Set values for insert/update statement.
 * @method  Query    where(mixed $where)     Add where clause.
 *
 * @method  integer  insert(mixed $values)   Insert row into table.
 * @method  boolean  update(array $values)   Update rows in table.
 * @method  boolean  delete(mixed $where)    Delete rows from table.
 * @method  array    column(string $column)  Get all values of a given column.
 * @method  object   first()                 Get first row in result set.
 * @method  array    all()                   Get all rows matching the query.
 * @method  integer  count()                 Get number of rows affected.
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Database
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
abstract class ModelAbstract {

  /** Error Codes */
  const ERROR_NO_DATABASE_CONNECTION = 0;
  const ERROR_MODEL_NOT_FOUND = 1;
  const ERROR_INVALID_PRIMARY_KEY = 2;

  /**
   * The name of the standard `created_at` column.
   *
   * @var string
   */
  const CREATED_AT = 'created_at';

  /**
   * The name of the standard `updated_at` column.
   *
   * @var string
   */
  const UPDATED_AT = 'updated_at';

  /**
   * Closure used to resolve a database connection when required.
   *
   * @var Closure
   */
  protected static $resolver;

  /**
   * Database connection used to perform model operations.
   *
   * @var Database
   */
  protected static $db;

  /**
   * The validation rules to apply to the attributes on save/update.
   *
   * @var  array
   */
  public static $rules = array();

  /**
   * The messages to display on validation errors.
   *
   * @var  array
   */
  public static $messages = array();

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table;

  /**
   * The primary key for the model.
   *
   * @var string
   */
  protected $primaryKey = 'id';

  /**
   * Indicates whether a database entry exists for the model.
   *
   * @var  boolean
   */
  protected $exists = false;

  /**
   * Contains the model's raw attribute data.
   *
   * @var array
   */
  protected $raw = array();

  /**
   * Contains all field instances. A field is instantiated on request to reduce
   * the initial setup scope.
   *
   * @var  array
   */
  protected $fields = array();

  /**
   * The attributes that should be visible in arrays.
   *
   * @var string[]
   */
  protected $visible = array();

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var string[]
   */
  protected $hidden = array();

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = array();

  /**
   * The attributes that represent timestamp values.
   *
   * @var  array
   */
  protected $timestamps = array( self::CREATED_AT, self::UPDATED_AT );

  /**
   * A collection of validation errors.
   *
   * @var  Comments\Support\Messages
   */
  protected $errors;

  /**
   * Expects a callback function to resolve a database connection once the user
   * performs the first database queries.
   *
   * @param   Closure  $cb  Connection resolver.
   */
  public static function resolver(Closure $cb) {
    self::$resolver = $cb;
  }

  /**
   * Retrieve the database connection to operate on. Resolve a connection if not
   * done before.
   *
   * @return  Database
   */
  public static function resolve() {

    // Return an already resolved database connection
    if (!is_null(static::$db)) {
      return static::$db;
    }

    // Resolve and cache the database connection
    $resolver = static::$resolver;
    if ($resolver instanceof Closure) {
      return static::$db = $resolver();
    }

    throw new Error('Could not resolve database connection.', self::ERROR_NO_DATABASE_CONNECTION);

  }

  /**
   * Save a new model and return the instance.
   *
   * @param  array  $attributes
   * @return static
   */
  public static function create(array $attributes) {

    $model = new static($attributes);
    $model->save();
    return $model;

  }

  /**
   * Create a new model instance from the $_POST input.
   *
   * @return  static
   */
  public static function fromInput() {

    $model = new static();
    return $model->fill( r::data() );

  }

  /**
   * Find a model by its primary key.
   *
   * @param  string|integer  $id
   * @return Collection|static|boolean
   */
  public static function find($id) {

    if (is_array($id) && empty($id)) {
      return new Collection();
    }

    $instance = new static();
    return $instance->query()->find($id);

  }

  /**
   * Find a model by its primary key or return a new instance.
   *
   * @param  string|integer  $id
   * @return Collection|static
   */
  public static function findOrCreate($id) {

    $model = static::find($id);

    if (!empty($model))
      return $model;

    return new static();

  }

  /**
   * Find a model by its primary key or throw an exception.
   *
   * @throws Error If model not found
   *
   * @param  string|integer  $id
   * @return Collection|static
   */
  public static function findOrFail($id) {

    $model = static::find($id);

    if (!empty($model))
      return $model;

    throw new Error('The database does not contain an entry for the given model', self::ERROR_MODEL_NOT_FOUND);

  }

  /**
   * Get all of the models from the database.
   *
   * @param  array  $columns
   * @return Collection
   */
  public static function all() {

    $instance = new static();
    return $instance->query()->all();

  }

  /**
   * Get the number of entries available for the model.
   *
   * @return  integer
   */
  public static function count() {
    return (new static())->query()->count();
  }

  /**
   * Constructor.
   *
   * Create a new database model instance.
   *
   * @param  array    $attributes  Attribute values.
   */
  public function __construct(array $attributes = array()) {
    $this->fill($attributes);
  }

  /**
   * Determine if the given attribute is visible.
   *
   * @internal
   * @param     string   $key
   * @return    boolean
   */
  public function isVisible($key) {

    if (in_array($key, $this->visible)) {
      return true;
    }

    return empty($this->visible);

  }

  /**
   * Determine if the given attribute is hidden.
   *
   * @internal
   * @param     string   $key
   * @return    boolean
   */
  public function isHidden($key) {

    if (in_array($key, $this->hidden)) {
      return true;
    }

    return !empty($this->hidden);

  }

  /**
   * Determine if the given attribute may be mass assigned.
   *
   * @internal
   * @param     string   $key
   * @return    boolean
   */
  public function isFillable($key) {

    if (in_array($key, $this->fillable)) {
      return true;
    }

    return empty($this->fillable);

  }

  /**
   * Get the primary key for the model.
   *
   * @return  string
   */
  public function primaryKeyName() {
    return $this->primaryKey;
  }

  /**
   * Get the value of the primary key for the model.
   *
   * @return  integer
   */
  public function primaryKeyValue() {
    return $this->get($this->primaryKeyName())->int();
  }

  /**
   * Get the database table name.
   *
   * @return  string
   */
  public function table() {

    if (empty($this->table)) {
      $class = a::last( Str::split( get_class($this), '\\' ) );
      return str_replace('\\', '_', Str::snakecase( Str::plural( $class )));
    }

    return $this->table;

  }

  /**
   * Get the collection of validation errors.
   *
   * @return  Comments\Support\Validation
   */
  public function errors() {

    // Lazy initialization if the message bag
    if (is_null($this->errors)) {
      $this->errors = new Messages();
    }

    return $this->errors;

  }

  /**
   * Get/set the visble attributes for the model.
   *
   * @param   string[]         $visible
   * @return  static|string[]
   */
  public function visible($visible = null) {

    if (is_array($visible)) {
      $this->visible = $visible;
      return $this;
    }

    return $this->visible;

  }

  /**
   * Get/set the hidden attributes for the model.
   *
   * @param   string[]         $hidden
   * @return  static|string[]
   */
  public function hidden($hidden = null) {

    if (is_array($hidden)) {
      $this->hidden = $hidden;
      return $this;
    }

    return $this->hidden;

  }

  /**
   * Get/set the fillable attributes for the model.
   *
   * @param   string[]         $fillable
   * @return  static|string[]
   */
  public function fillable($fillable) {

    if (is_array($fillable)) {
      $this->fillable = $fillable;
      return $this;
    }

    return $this->fillable;

  }

  /**
   * Get the raw value for a given attribute key. Returns all raw values if no
   * key is given.
   *
   * @param   string  $key
   * @return  mixed
   */
  public function raw($key = null) {

    if (is_null($key)) {
      return $this->raw;
    }

    if (isset($this->raw[$key])) {
      return $this->raw[$key];
    }

    return null;

  }

  /**
   * Get all attrubute data as key-value pairs. Alias for
   * ModelAbstract::toArray().
   *
   * @return  array
   */
  public function data() {
    return $this->toArray();
  }

  /**
   * Get a field instance by name.
   *
   * @param   string  $key  Name of the field to retrieve.
   *
   * @return  Field
   */
  public function get($key) {

    // We always have to return a field instance to make this call reliable.
    // Requests for attributes which do not exist will consequently return an
    // empty field instance.
    $value = $this->has($key) ? $this->raw[$key] : '';

    // Lazy instantiate a field to benefit from kirby’s field methods
    if (!isset($this->fields[$key])) {
      $this->fields[$key] = new Field($this->page(), $key, $value);
    }

    return $this->fields[$key];

  }

  /**
   * Check if a certain attribute is set on the current model instance.
   *
   * @param   string   $key  Name of the attribute to test for.
   * @return  boolean
   */
  public function has($key) {
    return array_key_exists($key, $this->raw);
  }

  /**
   * Update a field value.
   *
   * @param  string  $key    Name of the field to update.
   * @param  mixed   $value  Value of the field.
   */
  public function set($key, $value) {

    // Update raw attribute value
    $this->raw[$key] = $value;

    // Update field value in cache if already instantiated
    if (isset($this->fields[$key])) {
      $this->fields[$key]->value = $value;
    }

    return $this;

  }

  /**
   * Get formatted date fields. Defaults to the creation timestamp if available.
   *
   * @see     http://php.net/manual/en/function.date.php
   *
   * @param   string  $format  Date format.
   * @param   string  $key     Name of the date field to return.
   *
   * @return  Field|string
   */
  public function date($format = null, $key = null) {

    if (empty($this->timestamps)) {
      return false;
    }

    if (is_null($key)) {
      $key = isset($this->raw[self::UPDATED_AT]) ? self::UPDATED_AT : self::CREATED_AT;
    }

    return !is_null($format) ? date($format, $this->get($key)->value()) : $this->get($key);

  }

  /**
   * Fill the model with an array of attributes.
   *
   * @param  array  $attributes
   * @return static
   */
  public function fill(array $attributes) {

    if (count($this->fillable) > 0) {
      $whitelist  = array_flip($this->fillable);
      $attributes = array_intersect_key($attributes, $whitelist);
    }

    foreach ($attributes as $key => $value) {

      // NOTE: We check if the attribute is fillable here again to allow
      // extending classes to customize this process by overwriting the method.
      if ($this->isFillable($key)) {
        $this->set($key, $value);
      }

    }

    return $this;

  }

  /**
   * Perform validation on the model instance.
   *
   * @param   array   $rules     Validation rules to apply.
   * @param   array   $messages  Validation errors to display.
   *
   * @return  boolean
   */
  public function validate(array $rules = array(), array $messages = array()) {

    // Use the rules applied statically to the model as fallback
    if (empty($rules)) {
      $rules = static::$rules;
    }

    // Nothing to validate, return early
    if (empty($rules)) {
      return true;
    }

    // Use the messages applied statically to the model as fallback
    if (empty($messages)) {
      $messages = static::$messages;
    }

    // Perform model validation
    $validator = new Validator($this->data(), $rules, $messages);

    if ($validator->fails()) {

      $this->errors()->merge( $validator->errors() );
      return false;

    }

    return true;

  }

  /**
   * Save changes of the model back to the database.
   *
   * @param   boolean  $force Whether to ignore validation errors.
   * @return  boolean  True if saved successfully. False otherwise.
   */
  public function save($force = false) {

    // Return early if validation fails. Give the user the option to continue
    // regardless of the validation result.
    if (!$this->validate() && !$force) {
      return false;
    }

    $saved = false;
    $now = time();

    // Update `update_at` timestamp if used by the model
    if (in_array(self::UPDATED_AT, $this->timestamps)) {
      $this->set(self::UPDATED_AT, $now);
    }

    // Update an existing database entry
    if ($this->exists) {
      $saved = $this->entry()->update($this->data());
    }

    // Create a new database entry
    else {

      // Update `created_at` timestamp if used by the model
      if (in_array(self::CREATED_AT, $this->timestamps)) {
        $this->set(self::CREATED_AT, $now);
      }

      $id = $this->query()->insert($this->data());

      // Assign the primary key retrieved from the database
      if ($id !== false) {
        $this->set($this->primaryKeyName(), $id);
        $saved = true;
      }

    }

    return $this->exists = $saved;

  }

  /**
   * Delete the model from the database.
   *
   * @return  boolean  True if successfully removed. False otherwise.
   */
  public function delete() {

    if ($this->exists) {
      return $this->entry()->delete();
    }

    return false;

  }

  /**
   * Prepare a new query object.
   *
   * @return    \Database\Query
   */
  protected function query() {

    $connection = static::resolve();
    $prefix = $connection->prefix();

    $query = new Query($connection, $prefix . $this->table());
    $query->fail()->primaryKeyName($this->primaryKeyName())->fetch(get_class($this));

    return $query;

  }

  /**
   * Return a query object that has the current primary key already applied as
   * WHERE clause.
   *
   * @throws Error If the current model has no valid value for the primary key
   * @return \Database\Query
   */
  protected function entry() {

    $key = $this->primaryKeyName();
    $value = $this->primaryKeyValue();

    if (is_null($value)) {
      throw new Error('Invalid value for the primary key', self::ERROR_INVALID_PRIMARY_KEY);
    }

    return $this->query()->where(array($key => $value));

  }

  /**
   * Convert the model instance to an array.
   *
   * @return  array
   */
  public function toArray() {

    // Filter the attribute value
    if (count($this->visible) > 0) {
      $attributes = array_intersect_key($this->raw(), array_flip($this->visible));
    } else {
      $attributes = array_diff_key($this->raw(), array_flip($this->hidden));
    }

    $values = array();
    $fields = array_keys( $attributes );

    foreach ($fields as $key) {

      // Avoid creating new field instance while collecting the field values
      if (isset($this->fields[$key])) {
        $values[$key] = $this->fields[$key]->value;
      } else {
        $values[$key] = $this->raw($key);
      }

    }

    return $values;

  }

  /**
   * Convert the model instance to JSON.
   *
   * @param   integer  $options
   * @return  string
   */
  public function toJson($options = 0) {
    return json_encode($this->toArray(), $options);
  }

  /**
   * Convert the model to its string representation.
   *
   * @return  string
   */
  public function toString() {
    return (string)$this;
  }

  /**
   * Convert the model to its string representation.
   *
   * @return string
   */
  public function __toString() {
    return $this->toJson();
  }

  /**
   * Determine if an attribute exists on the model.
   *
   * @param   string  $key
   * @return  bool
   */
  public function __isset($key) {

    if (isset($this->raw[$key])) {
      return true;
    }

    $method = Str::camelcase($key);
    return method_exists($this, $method) && !is_null($this->{$method}());

  }

  /**
   * Dynamically retrieve attributes values from the model.
   *
   * NOTE: In kirby properties are usually retrieved via a getter method on the
   * object.
   *
   * @internal
   *
   * @param     string  $key
   * @return    mixed
   */
  public function __get($key) {
    return $this->raw($key);
  }

  /**
   * Dynamically set attribute values on the model.
   *
   * NOTE: In kirby properties are usually assigned via a setter method on the
   * object. This magic method is implemented in first place for the
   * PDO::FETCH_CLASS mode which tries to assign column values to the
   * corresponding properties of an object instance.
   *
   * @internal
   *
   * @param     string  $key
   * @param     mixed   $value
   */
  public function __set($key, $value) {

    $this->set($key, $value);

    // Assume the model exists in the database if we retrieve a valid primary
    // key. As the property setter should only be used by PDO on model
    // instantiation this should be a save assumption to distinguish between
    // user created models and query created comments.
    if (($key === $this->primaryKeyName()) && (!is_null($value))) {
      $this->exists = true;
    }

  }

  /**
   * Unset an attribute on the model.
   *
   * @param  string  $key
   */
  public function __unset($key) {

    unset($this->raw[$key]);
    unset($this->fields[$key]);

  }

  /**
   * Handle dynamic method calls.
   *
   * @param  string  $method
   * @param  array   $arguments
   *
   * @return Query|Field
   */
  public function __call($method, $arguments) {

    static $whitelist = array(
      'select', 'insert', 'update', 'delete', 'values', 'bindings', 'where',
      'count', 'min', 'max', 'sum', 'avg', 'first', 'all', 'column'
    );

    // Act as proxy for Database\Query methods
    if (in_array($method, $whitelist)) {
      return call_user_func_array(array($this->query(), $method), $arguments);
    }

    // Call the attribute getter/setter
    $attribute = Str::snakecase($method);

    if (empty($arguments)) {
      return $this->get($attribute);
    } else {
      array_unshift($arguments, $attribute);
      return call_user_func_array(array($this, 'set'), $arguments);
    }

  }

  /**
   * Handle dynamic static method calls.
   *
   * @param   string  $method
   * @param   array   $arguments
   *
   * @return  mixed
   */
  public static function __callStatic($method, $arguments) {

    $instance = new static();
    return call_user_func_array(array($instance, $method), $arguments);

  }

}
