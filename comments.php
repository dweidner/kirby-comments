<?php

/**
 * Bootstrap Comments Plugin.
 *
 * This file doesn't do anything, but loading the plugin core. By default the
 * plugin is launched automatically. The user has the chance to disable this
 * behavior via the site configuration to launch the plugin only on pages
 * requiring the plugin’s functionality.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 */

// Load plugin core
require_once(__DIR__ . DS . 'library' . DS . 'plugin.php');

// Initiate and launch comment system
global $plugins;

if (!is_array($plugins)) {
  $plugins = array();
}

if (!isset($plugins['comments'])) {
  $plugins['comments'] = new Comments\CommentPlugin(kirby());
}

if (c::get('comments.autoload', true)) {
  $plugins['comments']->launch();
}
