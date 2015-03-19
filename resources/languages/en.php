<?php

/**
 * Plugin Localization - English
 *
 * Allows to translate the plugin to various languages.
 *
 * @see  PluginAbstract::i18n()
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-1.0
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */

return array(
  'title' => 'English',
  'author' => 'Daniel Weidner <hallo@danielweidner.de>',
  'version' => '1.0.0',
  'data' => array(

    // Field Labels
    'comments.field.author'       => 'Name',
    'comments.field.author_email' => 'E-Mail',
    'comments.field.author_url'   => 'Website',
    'comments.field.username'     => 'Logged in as %1$s',
    'comments.field.text'         => 'Comment',
    'comments.field.required'     => 'Required',
    'comments.field.honeypot'     => 'Leave this field empty',

    // Form buttons
    'comments.button.send'        => 'Send Comment',

    // Comment Administration
    'comments.link.edit'          => 'Edit Comment',
    'comments.link.delete'        => 'Delete Comment',

    // Success messages
    'comments.success.saved'      => 'Comment saved',
    'comments.success.deleted'    => 'Comment deleted',

    // Error messages
    'comments.error.incomplete'   => 'Missing required fields',
    'comments.error.throttle'     => 'Number of allowed comments per interval exceeded',
    'comments.error.duplicate'    => 'Duplicate content',
    'comments.error.save'         => 'Could not save comment',
    'comments.error.delete'       => 'Could not delete comment',
    'comments.error.notfound'     => 'Comment not found',

    // Validation Messages
    'comments.validator.fallback' => 'Invalid value for {key}',

  ),
);
