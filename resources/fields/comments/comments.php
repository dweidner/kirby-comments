<?php

/**
 * Comments Field
 *
 * A custom field to display all comments of a certain page.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class CommentsField extends CheckboxField {

  /**
   * Field assets to load.
   *
   * @var  array
   */
  public static $assets = array(
    'js' => array('model.js', 'controller.js', 'field.js'),
    'css' => array('field.css'),
  );

  /**
   * Constructor.
   *
   * Create a new field instance.
   */
  public function __construct() {
    $this->load();
  }

  /**
   * Load field dependencies.
   *
   * @return array
   */
  public function load() {

    $loaded = array();
    $dependencies = array('comments');
    foreach ($dependencies as $d) {
      $loaded[] = kirby()->plugin($d);
    }

    return $loaded;

  }

  /**
   * Render input field.
   *
   * @return  Brick
   */
  public function input() {

    // Provide a default label for the checkbox
    $text = $this->text() ? $this->text() : 'Disable comments?';

    // Build the input field
    $wrapper = parent::input();
    $input   = $wrapper->html();

    $wrapper->text($text);
    $wrapper->prepend($input);

    return $wrapper;

  }

  /**
   * Render comment list.
   *
   * @return  string
   */
  public function comments() {

    $html = '';
    $comments = comment::findByPage($this->page)->page(1, 10);

    if ($comments->count() > 0) {

      $html .= '<div class="items comments">';

      foreach($comments as $comment){

        $html .= tpl::load(__DIR__ . DS . 'template.php', array(
          'field' => $this,
          'comment' => $comment,
        ));

      }

      $html .= '</div>';

    }


    return $html;

  }

  /**
   * Render the edit comment form.
   *
   * @return  string
   */
  public function editForm() {
    return tpl::load(__DIR__ . DS . 'form.php', array('field' => $this));
  }

  /**
   * Render custom field.
   *
   * @return  Brick
   */
  public function content() {

    $content = parent::content();
    $content->append('<br>');
    $content->append($this->comments());
    $content->append($this->editForm());

    return $content;

  }

}
