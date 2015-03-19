<?php

namespace Comments\View;

/**
 * Comment Walker
 *
 * This class walks down a given comment hierarchy and renders all elements
 * on the path. Resembles the Walker class in WordPress.
 *
 * @see http://codex.wordpress.org/Class_Reference/Walker
 *
 * @package     Kirby CMS
 * @subpackage  Comments\View
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Walker {

  /**
   * Constructor.
   *
   * Create a new comment walker instance.
   */
  public function __construct() {

    // Empty

  }

  /**
   * Start walking the hierarchy starting with the given elements.
   *
   * @param   Comments  $comments Comment collection to process.
   * @return  string              Generated output.
   */
  public function walk( $comments ) {

    $output = '';

    foreach ( $comments as $comment ) {
      $this->render( $output, $comment );
    }

    return $output;

  }

  /**
   * Render a comment and all its child elements.
   *
   * @param   string   $output   Generated output.
   * @param   Comment  $comment  Comment to render.
   * @param   integer  $level    Current level in the hierarchy.
   */
  protected function render(&$output, $comment, $level = 0) {

    $this->onCommentStart($output, $comment, $level);

    foreach ($comment->children() as $child) {

      // Start a new level if not already done
      if (!isset($started)) {
        $started = true;
        $this->onLevelStart($output, $level);
      }

      $this->render($output, $child, $level + 1);
    }

    // Close the current level
    if (isset($started) && $started) {
      $this->onLevelEnd($output, $level);
    }

    $this->onCommentEnd($output, $comment, $level);

  }

  /**
   * Callback function triggered at the beginning of the rendering process of
   * an individual comment.
   *
   * @param   string   $output   Generated output.
   * @param   Comment  $comment  Comment to render.
   * @param   integer  $level    Current level in the hierarchy.
   */
  protected function onCommentStart(&$output, $comment, $level = 0) {

    if ($template = plugin('comments')->finder()->locate('comment')) {

      ob_start();
      require($template);
      $output .= ob_get_clean();

    }

  }

  /**
   * Callback function triggered at the end of the rendering process of
   * an individual comment.
   *
   * @param   string   $output   Generated output.
   * @param   Comment  $comment  Comment to render.
   * @param   integer  $level    Current level in the hierarchy.
   */
  protected function onCommentEnd(&$output, $comment, $level = 0) {

    // Virtual

  }

  /**
   * Callback function triggered at the beginning of the rendering process of
   * a new hierarchy level.
   *
   * @param   string   $output   Generated output.
   * @param   integer  $level    Current level in the hierarchy.
   */
  protected function onLevelStart(&$output, $level = 0) {

    // Virtual

  }

  /**
   * Callback function triggered at the end of the rendering process of
   * a new hierarchy level.
   *
   * @param   string   $output   Generated output.
   * @param   integer  $level    Current level in the hierarchy.
   */
  protected function onLevelEnd(&$output, $level = 0) {

    // Virtual

  }

}
