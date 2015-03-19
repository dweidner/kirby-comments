<?php

namespace Comments;

use Page;

/**
 * Page Controller
 *
 * @todo PageController class description
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class PageController extends Controller {

  /**
   * Get the hash value for a page object.
   *
   * @param   string      $uri  Page uri.
   * @return  string
   */
  public function hash($uri) {

    $page = site()->page($uri);

    if ($page instanceof Page) {

      return $this->success(array(
        'uri'  => $page->uri(),
        'hash' => $page->hash(),
      ));

    } else {

      return $this->error('Page not found', 400, array(
        'uri' => $page->uri(),
      ));

    }

  }

}
