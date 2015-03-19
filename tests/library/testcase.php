<?php

/**
 * Base Test Case
 *
 * @todo  class description
 *
 * @package     Kirby CMS
 * @subpackage  Comments\Tests
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class CommentPluginTestCase extends PHPUnit_Framework_TestCase {

  public function kirbyInstance($options = array()) {

    c::$data = array();

    $kirby = new Kirby($options);
    $kirby->roots->content = TEST_ROOT_ETC . DS . 'content';

    return $kirby;

  }

  public function siteInstance($kirby = null, $options = array()) {

    $kirby = !is_null($kirby) ? $kirby : $this->kirbyInstance($options);
    $site  = new Site($kirby);

    return $site;

  }

  public function pluginInstance($kirby = null, $options = array()) {

    $kirby = !is_null($kirby) ? $kirby : $this->kirbyInstance($options);
    $plugin = new Comments\CommentPlugin($kirby);

    return $plugin;

  }
}
