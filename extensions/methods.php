<?php

/**
 * Allows to obfuscate E-Mail addresses. At least we try to make it a bit more
 * more difficult for bots to gather our mail addresses.
 *
 * @param  Field  $field  The calling Kirby Field instance.
 */
field::$methods['obfuscate'] = function($field) {
  return str::encode($field->value());
};

/**
 * Removes all HTML tags from the field value before parsing the field as
 * markdown. Encodes all special characters of the resulting string as html
 * entities to allow only a predefined list of tags. This method should be used
 * to allow markdown in user generated contents.
 *
 * @see  http://shiflett.org/blog/2007/mar/allowing-html-and-preventing-xss
 *
 * @param  Field  $field  The calling Kirby Field instance.
 * @param  array  $tags   List of html tags to allow.
 *
 * @return Field
 */
field::$methods['safeMarkdown'] = function($field, $tags = null) {

  // Sensible default for user generated contents
  if (!is_array($tags)) {
    $tags = array('a', 'p', 'em', 'strong', 'ul', 'ol', 'li', 'code', 'pre', 'blockquote');
  }

  // Ensure the string is utf-8 encoded to protect against XSS exploits using
  // different encodings.
  $text = $field->value();
  $encoding = str::encoding($text);

  if (strtolower($encoding) !== 'utf-8') {
    $text = str::convert($text, 'UTF-8//IGNORE', $encoding);
  }

  // Strip all raw html tags from the input, but allow them in code blocks
  if (in_array('code', $tags)) {
    $text = preg_replace_callback('/`[^`]+`|`{3}[^`]+`{3}|~{3}[^~]+~{3}/', function($m) { return str_replace(array('<', '>'), array('&lt;', '&gt;'), $m[0]); }, $text);
  }
  $text = strip_tags($text);

  // Setup markdown parser
  $parsedown = new Parsedown();
  $parsedown->setBreaksEnabled(true);

  // Parse markdown and escape output. Now it is safe by default.
  $html = $parsedown->text($text);
  $html = htmlentities($html, ENT_COMPAT, 'utf-8');

  // Convert links with specific protocols and a limited set of attributes
  if (in_array('a', $tags)) {
    $html = preg_replace('!&lt;a +href=&quot;((?:ht|f)tps?://.*?)&quot;(?: +title=&quot;(.*?)&quot;)? *&gt;(.*?)&lt;/a&gt;!m', '<a href="$1" title="$2" rel="nofollow">$3</a>', $html);
    $index = array_search('a', $tags);
    unset($tags[$index]);
  }

  // Convert allowed tags
  if (!empty($tags)) {
    $html = preg_replace('!&lt;br */?&gt;!m', '<br/>', $html);
    $html = preg_replace('!&lt;(/?)(' . implode('|', $tags) . ')&gt;!', '<$1$2>', $html);
  }

  // Code blocks are double encoded. Make them readable again by replacing
  // the ambersand html entity with the corresponding character.
  if (in_array('code', $tags)) {
    $html = preg_replace_callback('!<code>[^<]+</code>!', function($m) { return str_replace(array('&amp;amp;lt;', '&amp;amp;gt;'), array('&lt;', '&gt;'), $m[0]); }, $html);
  }

  // Update field value as we are done
  $field->value = $html;

  return $field;

};
