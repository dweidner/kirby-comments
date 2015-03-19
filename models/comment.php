<?php

use Comments\Database\ModelAbstract;

/**
 * Comment Model
 *
 * This class provides an abstraction layer for an individual comment entry in
 * the database. Its primary purpose is to provide an easy way to query for
 * comments using different filter parameter. Furthermore the class provides
 * methods to synchronize changes of a model instance with the corresponding
 * entries in a database.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Comment extends ModelAbstract {

  /** Status Codes */
  const STATUS_UNAPPROVED = 0;
  const STATUS_APPROVED = 1;
  const STATUS_SPAM = 2;
  const STATUS_TRASH = 3;

  /**
   * The validation rules to apply to the attributes on save/update.
   *
   * @var  array
   */
  public static $rules = array(
    'status'       => 'integer',
    'text'         => 'required',
    'author'       => 'requiredWithout:username|max:255',
    'author_email' => 'requiredWithout:username|email',
    'author_url'   => 'url',
    'author_ip'    => 'ip',
    'author_agent' => 'max:255',
    'username'     => 'user',
    'rating'       => 'integer',
  );

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = array(
    'text',
    'author',
    'author_email',
    'author_url',
    'author_ip',
    'author_agent',
  );

  /**
   * Parent comment.
   *
   * @var  self
   */
  protected $parent;

  /**
   * Collection of child nodes.
   *
   * @var  Comments
   */
  protected $children;

  /**
   * A registered user that has created the comment.
   *
   * @var  User|null
   */
  protected $user;

  /**
   * Find all comments for a certain page.
   *
   * @param  string|Page  $page
   * @return Collection|static|boolean
   */
  public static function findByPage($page) {

    // Try to retrieve the page object via uri or hash value
    if (is_string($page)) {
      $page = (false !== strpos($page, '/')) ? site()->page($page) : site()->index()->findBy('hash', $page);
    }

    // Page not found? Simply return an empty collection.
    if (!($page instanceof Page)) {
      return new Comments();
    }

    // Query for all comments for the given page.
    return static::where(array('page_uri' => $page->uri()));

  }

  /**
   * Constructor.
   *
   * Create a new comment instance.
   *
   * @param  array    $attributes  Attribute values.
   */
  public function __construct(array $attributes = array()) {

    parent::__construct($attributes);

    $this->set('status', self::STATUS_UNAPPROVED);
    $this->set('rating', 0);

  }

  /**
   * Check if the contents of the comment is approved by a moderator.
   *
   * @return  boolean
   */
  public function isApproved() {
    return $this->get('status')->int() === self::STATUS_APPROVED;
  }

  /**
   * Check if the comment is awaiting approval through a moderator.
   *
   * @return  boolean
   */
  public function isWaiting() {
    return !$this->isApproved();
  }

  /**
   * Check if the comment is classified as Spam comment.
   *
   * @return  boolean
   */
  public function isSpam() {
    return $this->get('status')->int() === self::STATUS_SPAM;
  }

  /**
   * Get the unique id of the current comment instance.
   *
   * @return  integer
   */
  public function id() {
    return $this->get('id')->int();
  }

  /**
   * Get the uniqe id of the parent id. Only returns a value if threaded
   * comments are enabled.
   *
   * @return  integer
   */
  public function parentId() {
    return $this->get('parent_id')->int();
  }

  /**
   * Get the page the comment is assigned to.
   *
   * @return  Page
   */
  public function page() {
    return site()->page( $this->raw('page_uri') );
  }

  /**
   * Get the uri for the comment.
   *
   * @return  string
   */
  public function uri() {
    return $this->page()->uri() . '#comment-' . $this->id();
  }

  /**
   * Get the full url for the comment.
   *
   * @return  string
   */
  public function url() {
    return url( $this->uri() );
  }

  /**
   * Get the url for an API endpoint.
   *
   * @param   string  $action
   * @param   boolean $ajax
   *
   * @return  string
   */
  public function actionUrl($action, $ajax = false) {

    $hash = $this->page()->hash();
    $id   = $this->id();

    if (!$ajax) {
      return url("api/pages/${hash}/comments/${action}/${id}");
    }

    return url("api/pages/${hash}/comments/${id}");

  }

  /**
   * Get the url to manipulate the comment in the panel.
   *
   * @param   boolean  $action
   * @return  string
   */
  public function panelUrl($action) {

    $uri = $this->page()->uri();
    $id  = $this->id();

    return url("panel/#/pages/show/${uri}/c:${id}/action:${action}");

  }

  /**
   * Get/set the parent node for the current comment.
   *
   * @param   self  $value  Parent node.
   * @return  self
   */
  public function parent($value = false) {

    if ($value instanceof Comment) {
      $this->parent = $value;
      $this->set('parent_id', $value->id());
    } else if (is_null($value) || is_numeric($value)) {
      $this->parent = null;
      $this->set('parent_id', $value);
    }

    return $this->parent;

  }

  /**
   * Get collection of children.
   *
   * @return  Comments
   */
  public function children() {

    // Lazy initialization
    if (is_null($this->children)) {
      $this->children = new Comments();
    }

    return $this->children;

  }

  /**
   * Get the level of the comment within the hierarchy.
   *
   * @var  integer
   */
  public function depth() {
    return !is_null($this->parent()) ? $this->parent()->depth() + 1 : 0;
  }

  /**
   * Return the user that has created the comment. Will return null if an
   * anonymous user has created the comment.
   *
   * @return  User|boolean
   */
  public function user() {

    // Use cached result if available
    if ( !is_null( $this->user ) ) {
      return $this->user;
    }

    // Retrieve user object only once
    $user = site()->user( $this->raw('username') );
    return $this->user = ( $user instanceof User  ? $user : false );

  }

  /**
   * Alias for Comment::userCan().
   *
   * @param   string   $action
   * @return  boolean
   */
  public function currentUserCan($action) {
    return $this->userCan( user::current(), $action );
  }

  /**
   * Test if a given user has capability to perform a certain action.
   *
   * @param   User     $user
   * @param   string   $action
   * @return  boolean
   */
  public function userCan($user, $action) {

    // Ensure a valid user object is given
    if (!($user instanceof User)) {
      return false;
    }

    // Test for authorship
    $isAuthor = ($this->username() === $user->username());

    if ( $isAuthor && in_array($action, array('edit', 'delete')) ) {
      return true;
    }

    // Test if the user has the required role
    $caps  = plugin('comments')->config()->get('capabilities');
    $roles = explode( '|', a::get( $caps, $action ) );
    return in_array( 'all', $roles ) || in_array( $user->role(), $roles );

  }

  /**
   * Get a profile image.
   *
   * @param   integer $size     Size of the image to display.
   * @param   string  $default  Image to load if no gravatar available.
   *
   * @return  Brick
   */
  public function gravatar($size = 64, $default = 'mm') {

    $img = new Brick('img');
    $img->addClass('comment-avatar');
    $img->attr('src', gravatar($this->authorEmail(), $size, $default));
    $img->attr('width', $size);
    $img->attr('height', $size);
    $img->attr('alt', '');

    return $img;

  }

  /**
   * Get a link to the panel that allows to edit the current comment. Only
   * available to registered users.
   *
   * @return  Brick|string
   */
  public function editLink() {

    // Ensure the current user is allowed to edit the comment
    if ( !$this->exists || !$this->currentUserCan('update') )
      return '';

    // Ensure the user has access to the panel
    if ( !($user = user::current()) || !$user->hasPanelAccess())
      return '';

    $link = new Brick('a');
    $link->attr('href', $this->panelUrl('edit'));
    $link->addClass('comment-update-link');
    $link->text(l('comments.comment.edit', 'Edit'));

    return $link;

  }

  /**
   * Get a link that allows to delete the comment. Only available to registered
   * users.
   *
   * @return  Brick|string
   */
  public function deleteLink() {

    // Ensure the current user is allowed to edit the comment
    if ( !$this->exists || !$this->currentUserCan('delete') ) {
      return '';
    }

    $uri  = $this->page()->uri();
    $hash = $this->page()->hash();
    $id   = $this->id();

    $link = new Brick('a');
    $link->attr('href', $this->actionUrl('delete'));
    $link->data('href', $this->actionUrl('delete', true));
    $link->addClass('comment-delete-link js-delete');
    $link->text(l('comments.comment.delete', 'Delete'));

    return $link;

  }

  /**
   * Prepare a new query object.
   *
   * @return    \Database\Query
   */
  protected function query() {
    return parent::query()->iterator('Comments');
  }

}
