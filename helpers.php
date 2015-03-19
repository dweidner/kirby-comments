<?php

/**
 * Global Helper Functions
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */

if (!function_exists('plugin')):

/**
 * Retrieve a plugin instance by name.
 *
 * @param   string  $name  Name of the plugin.
 * @return  mixed
 */
function plugin($name = null) {

  global $plugins;

  if (is_null($name)) {
    return $plugins;
  }

  return isset($plugins[$name]) ? $plugins[$name] : null;

}

endif;

if (!function_exists('comments')):

/**
 * Retrieve all comments for the current page.
 *
 * @param   string  $page_uri  Page uri.
 * @param   array   $args      Optional arguments.
 *
 * @return  Comments
 */
function comments($page_uri = null, $args = array()) {

  if (is_null($page_uri)) {
    $page_uri = page()->uri();
  }

  // Customize query to perform
  $defaults = array(
    'walker'     => null,
    'echo'       => true,
    'author'     => false,
    'user'       => false,
    'order_by'   => 'id',
    'order'      => 'DESC',
    'unapproved' => true,
    'page'       => 1,
    'per_page'   => 20,
  );
  $args = array_merge($defaults, $args);
  extract($args, EXTR_SKIP);

  // Prepare query
  $query = comment::findByPage($page_uri);

  // Approved comments only
  if ($unapproved === false) {
    $query->andWhere('status', '=', Comment::STATUS_APPROVED);
  }

  // Specifc author only
  if (!empty($author)) {
    $query->andWhere('author', 'LIKE', $author);
  }

  // Specifc user only
  else if (!empty($user)) {
    $user = ( $user instanceof User ) ? $user->username() : $user;
    $query->andWhere('username', 'LIKE', $user);
  }

  // Order by clause
  if (in_array($order_by, array('id', 'created_at', 'updated_at'))) {
    $order = ( strtoupper($order) === 'DESC' ) ? $order : 'ASC';
    $query->order($order_by . ' ' . $order);
  }

  // Perform query
  $comments = $query->page($page, $per_page);

  // Render comments if requested
  if ($echo) {

    $walker = !is_null($walker) ? new $walker() : new Comments\View\Walker();
    $output = $walker->walk($comments);

    echo $output;

  }

  return $comments;

}

endif;

if (!function_exists('commentForm')):

/**
 * Render the comment form.
 *
 * @param   array  $options  Form options.
 * @return  Brick
 */
function commentForm($options = array()) {
  echo new comments\view\form();
}

endif;
