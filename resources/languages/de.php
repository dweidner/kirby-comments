<?php

/**
 * Plugin Localization - German
 *
 * Allows to translate the plugin to various languages.
 *
 * @see  PluginAbstract::i18n()
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-0.1
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */

return array(
  'title' => 'Deutsch',
  'author' => 'Daniel Weidner <hallo@danielweidner.de>',
  'version' => '1.0.0',
  'data' => array(

    // Field Labels
    'comments.field.author'       => 'Name',
    'comments.field.author_email' => 'E-Mail',
    'comments.field.author_url'   => 'Website',
    'comments.field.username'     => 'Angemeldet als %1$s',
    'comments.field.text'         => 'Kommentar',
    'comments.field.required'     => 'Pflichtfeld',
    'comments.field.honeypot'     => 'Feld bitte freilassen',

    // Form buttons
    'comments.button.send'        => 'Kommentar abschicken',

    // Comment Administration
    'comments.link.edit'          => 'Kommentar bearbeiten',
    'comments.link.delete'        => 'Kommentar löschen',

    // Success messages
    'comments.success.saved'      => 'Kommentar gespeichert',
    'comments.success.deleted'    => 'Kommentar gelöscht',

    // Error messages
    'comments.error.incomplete'   => 'Missing required fields',
    'comments.error.throttle'     => 'Anzahl an erlaubten Kommentaren pro Zeitinterval überschritten',
    'comments.error.duplicate'    => 'Ein Kommentar mit dem selben Inhalt existiert bereits',
    'comments.error.save'         => 'Kommentar konnte nicht gespeichert werden',
    'comments.error.delete'       => 'Kommentar konnte nicht gelöscht werden',
    'comments.error.notfound'     => 'Kommentar konnte nicht gefunden werden',

    // Validation Messages
    'comments.validator.fallback' => 'Ungültiger Wert für {key}',

  ),
);
