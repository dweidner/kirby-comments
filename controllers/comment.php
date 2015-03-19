<?php

namespace Comments;

use A;
use C;
use R;
use User;
use Visitor;
use Comment;
use Database\Query;

use Comments\Support\Akismet;

/**
 * Comment Controller
 *
 * Plugin controller responsable for the creation, retrieval, alteration and
 * removal of comments from the database. Validates the data send by the user
 * and generates the corresponding response object.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class CommentController extends Controller {

  /**
   * Get a list of comments.
   *
   * @param   string      $hash  Unique hash value of the parent page.
   * @return  Collection
   */
  public function index($hash) {

    // Retrieve the page object via the given hash value
    $page = $this->findPageByHash($hash);

    // Query for all comments for the given page
    $comments = $this->findComments($page->uri());

    // Generate response
    return $this->success(array(
      'collection' => $comments,
      'pagination' => $comments->pagination(),
    ));

  }

  /**
   * Get the specified comment.
   *
   * @param   string     $hash  Unique hash value of the parent page.
   * @param   integer    $id    Id of the comment to retrieve.
   *
   * @return  Response
   */
  public function show($hash, $id) {

    $page = $this->findPageByHash($hash);
    $comment = comment::find($id);

    if ($comment instanceof $comment) {
      return $this->success(array('comment' => $comment->toArray()));
    } else {
      $msg = l('comments.error.notfound', 'Comment not found');
      return $this->error($msg, 404, array('id' => $id));
    }

  }

  /**
   * Store a new comment in the database.
   *
   * @param   string     $hash  Unique hash value of the parent page.
   * @return  Response
   */
  public function create($hash) {

    // Retrieve the parent page
    $page = $this->findPageByHash($hash);

    // Create a comment from the post data
    $comment = comment::fromInput();
    $comment->set('page_uri', $page->uri());

    // Collect user information
    $comment->set('author_ip', visitor::ip());
    $comment->set('author_agent', visitor::ua());

    // Handle signed-in users
    if ($user = user::current()) {

      $fullname = trim($user->firstname() . ' ' . $user->lastname());
      $fullname = empty($fullname) ? $user->username() : $fullname;

      $comment->set('author', $fullname);
      $comment->set('author_email', $user->email());
      $comment->set('username', $user->username());

    }

    // Ensure the required comment fields are set
    if (!$comment->validate()) {

      $msg = l('comments.error.incomplete', 'Missing required fields');
      return $this->error($msg, 400, array(
        'input' => $comment->toArray(),
        'errors' => $comment->errors()->toArray(),
      ));

    }

    // Check the honeypot fields. Pretend everything went fine.
    if ($this->isBot()) {
      return $this->success();
    }

    // Throttle comment posting
    if ($this->isPartOfFlood($comment)) {

      $msg = l('comments.error.throttle', 'Number of allowed comments per interval exceeded');
      return $this->error($msg, 429, array(
        'input'  => $comment->toArray(),
        'errors' => array('other' => $msg),
      ));

    }

    // Check for duplicate contents
    if ($this->isDuplicate($comment)) {

      $msg = l('comments.error.duplicate', 'Duplicate content');
      return $this->error($msg, 409, array(
        'input'  => $comment->toArray(),
        'errors' => array('text' => $msg),
      ));

    }

    // Classify comment as spam or ham using Akismet. In addition allow to
    // blacklist authors.
    $discard = false;

    if ($this->isSpam($comment, $discard) || $this->isBlocked($comment)) {
      $comment->set('status', Comment::STATUS_SPAM);
    }

    // Save the comment to the database. Pretend the comment was saved
    // successfully for comments containing `blatant spam`.
    if (($discard && $comment->isSpam()) || $comment->save()) {

      $msg = l('comments.success.saved', 'Comment saved');
      return $this->success($msg, 201, array('id' => $comment->id()));

    } else {

      $msg = l('comments.error.save', 'Could not save comment');
      return $this->error($msg, 400, array(
        'input'  => $comment->toArray(),
        'errors' => $comment->errors()->toArray(),
      ));

    }

  }

  /**
   * Update the specified comment entry in the database.
   *
   * @param   string   $hash  Unique hash value of the parent page.
   * @param   integer  $id    Id of the comment to retrieve.
   *
   * @return  Response
   */
  public function update($hash, $id) {

    $page = $this->findPageByHash($hash);
    $comment = comment::find($id);

    if (!$comment) {

      $msg = l('comments.error.notfound', 'Comment not found');
      return $this->error($msg, 400, array('id' => $id, 'hash' => $hash));

    }

    $data = r::data();
    $comment->fill($data);

    if ($comment->save()) {

      $msg = l('comments.success.saved', 'Comment saved');
      return $this->success($msg, 200, array('id' => $comment->id()));

    } else {

      $msg = l('comments.error.save', 'Could not save comment');
      return $this->error($msg, 400, array(
        'input'  => $comment->toArray(),
        'errors' => $comment->errors()->toArray(),
      ));

    }

  }

  /**
   * Remove the specified comment from the database.
   *
   * @param   string     $hash  Unique hash value of the parent page.
   * @param   integer    $id    Id of the comment to retrieve.
   *
   * @return  Response
   */
  public function delete($hash, $id) {

    $page = $this->findPageByHash($hash);
    $comment = comment::find($id);

    if ($comment && $comment->delete()) {
      $msg = l('comments.success.deleted', 'Comment deleted');
      return $this->success($msg, 200, array('id' => $id));
    } else {
      $msg = l('comments.error.delete', 'Could not delete comment');
      return $this->error($msg, 400, array('id' => $id));
    }

  }

  /**
   * Report a comment as spam and remove it from the database.
   *
   * @param   string     $hash  Unique hash value of the parent page.
   * @param   integer    $id    Id of the comment to retrieve.
   *
   * @return  Response
   */
  public function ban($hash, $id) {

    $page = $this->findPageByHash($hash);
    $comment = comment::find($id);

    $this->reportSpam($comment);
    return $this->delete($hash, $id);

  }

  /**
   * Retrieve the page via a hash value created from its uri.
   *
   * @param   string  $hash  Unique hash value of the page.
   * @return  Page
   */
  protected function findPageByHash($hash) {

    $page = site()->index()->findBy('hash', $hash);

    if(!$page) {
      return $this->redirect('404');
    }

    return $page;

  }

  /**
   * Get all comments for a given page uri. Respects optional request parameter.
   *
   * @param   string  $uri  Page uri
   * @return  Collection
   */
  protected function findComments($uri) {

    // Take optional url paramaters into account
    $page = get('page', 1);
    $perPage = get('per_page', 30);

    return comment::findByPage($uri)->page($page, $perPage);

  }

  /**
   * Test if someone is flooding the database with comments to save resources.
   *
   * @param   Comment   $comment  Comment to test.
   * @return  boolean             Indicates whether a comment is part of a comment flood.
   *
   * @return  boolean
   */
  protected function isPartOfFlood($comment) {

    $comment = comment::select('created_at')
                   ->where('author_ip', '=', $comment->authorIp())
                   ->orWhere('author_email', '=', $comment->authorEmail())
                   ->order('created_at DESC')
                   ->first();

    $now  = time();
    $last = !empty($comment) ? $comment->createdAt()->int() : 0;
    $threshold = c::get('comments.throttle', 1);

    return $threshold > 0 && $last > 0 && (($now - $last) < $threshold);

  }

  /**
   * Test for duplicate comments.
   *
   * @param   Comment   $comment  Comment to test.
   * @return  boolean             Indicates whether a comment with the same contents exists.
   */
  protected function isDuplicate($comment) {

    return (boolean)comment::select('id')
                  ->where(function($q) use ($comment) {
                    return $q
                      ->where('author', '=', $comment->author())
                      ->orWhere('author_email', '=', $comment->authorEmail());
                  })
                  ->andWhere('text', '=', $comment->text())
                  ->limit(1)
                  ->count();

  }

  /**
   * Test if the comment author is on the blacklist and thereby not permitted to
   * post comments.
   *
   * @param   Comment   $comment  Comment to test.
   * @return  boolean             Indicates whether the comment author has been blocked.
   */
  protected function isBlocked($comment) {

    $blacklist = c::get('comments.blacklist', array());

    // Nothing to block
    if (empty($blacklist)) {
      return false;
    }

    // Test the given set of comment attributes for suspect contents
    static $attributes = array(
      'author',
      'author_ip',
      'author_email',
      'author_agent',
      'author_url',
      'text'
    );

    foreach ($blacklist as $item) {
      $pattern = preg_quote($item, '#');
      foreach ($attributes as $key) {
        if ($comment->has($key) && preg_match($pattern, $comment->raw($key))) {
          return true;
        }
      }
    }

    // Comment seems to be fine, according to our blacklist.
    return false;

  }

  /**
   * Test if any of the honeypot fields are filled.
   *
   * @return  boolean
   */
  protected function isBot() {

    // Honeypot spam prevention
    $config = $this->hub()->config();
    $method = $config->get('honeypot');

    switch ($method) {
      case 'css':
        $field = $config->get('honeypot.name', 'url');
        $value = r::get($field);
        return !empty($value);

      case 'js':
        $field = $config->get('honeypot.name', 'legit');
        $value = r::get($field);
        return 1 !== intval($value);
    }

    // Time based spam prevention
    $threshold = $config->get('requiredReadingTime', 0);

    if ($threshold > 0) {

      $now  = time();
      $time = r::get('tictoc');

      return ($now - $time) < $threshold;

    }

    return false;

  }

  /**
   * Use akismet to classify comments as spam or ham.
   *
   * @param   Comment  $comment  Comment to test.
   * @param   boolean  $discard  Indicates whether it is safe to discard the comment directly.
   *
   * @return  boolean
   */
  protected function isSpam($comment, &$discard = false) {

    $config = $this->hub()->config();

    // Test comment contents using the Akismet API
    $blog = site()->url();
    $key  = $config->get('akismet.key');
    $strictness = $config->get('akismet.strictness');

    if (empty($key)) {
      return false;
    }

    // Check the given comment contents using Akismet.
    $akismet = new Akismet($key, $blog);

    if ('discard' === $strictness) {
      $ip = $comment->raw('user_ip');
      $agent = $comment->raw('user_agent');
      return $akismet->isSpam($comment, $ip, $agent, $discard);
    } else {
      return $akismet->isSpam($comment);
    }

  }

  /**
   * Report comments that should have been marked as spam.
   *
   * @param   Comment  $comment  Spam comment.
   * @return  boolean
   */
  protected function reportSpam($comment) {

    $blog = site()->url();
    $key  = $this->hub()->config()->get('akismet.key');

    $akismet = new Akismet($key, $blog);
    return $akismet->submitSpam($comment);

  }

  /**
   * Report a false positive to help improve the classification.
   *
   * @param   Comment  $comment  Falsy classified comment.
   * @return  boolean
   */
  protected function reportHam($comment) {

    $blog = site()->url();
    $key  = $this->hub()->config()->get('akismet.key');

    $akismet = new Akismet($key, $blog);
    return $akismet->submitHam($comment);

  }

}
