<?php

/**
 * Comments Collection
 *
 * This class represents a collection of comments structured in a hierarchical
 * tree when threaded comments are enabled.
 *
 * @package     Kirby CMS
 * @subpackage  Comments
 * @since       2.x-1.0
 * @uses        Comments\PluginAbstract class
 *
 * @author      Daniel Weidner <hallo@danielweidner.de>
 * @link        http://github.com/dweidner/kirby-comments/
 */
class Comments extends Collection {

  /**
   * Constructor
   *
   * Create a new collection instance containing comments.
   *
   * @param  array  $comments  Comments to add to the collection.
   */
  public function __construct($comments = array()) {

    $this->data = array();

    if (!empty($comments) && is_array($comments)) {

      $tree = $this->tree($comments);
      $this->walk($tree);

    }

  }

  /**
   * Generate a tree hierarchy from the plain comment collection.
   *
   * @param   array  $comments  Plain comment array.
   * @return  array
   */
  protected function tree($comments) {

    $tree = array();

    $registry = array();
    $replies  = array();
    $orphans  = array();

    foreach ( $comments as $c ) {

      // Filter for comments
      if (!($c instanceof Comment)) {
        continue;
      }

      // Create an entry in the registry
      $registry[ $c->id() ] = array('node' => $c, 'children' => array());

      // Differentiate between toplevel comments and replies
      if ($c->parentId() > 0) {
        $replies[ $c->id() ] = $c;
      } else {
        $tree[ $c->id() ] = &$registry[ $c->id() ];
      }

    }

    // Sort replies into the tree. Identify orphans.
    foreach ( $replies as $c ) {

      if (isset($registry[ $c->parentId() ])) {
        $registry[ $c->parentId() ]['children'][] = &$registry[ $c->id() ];
      } else {
        $orphans[ $c->id() ] = &$registry[ $c->id() ];
      }

    }

    // Add orphans to the tree
    if (!empty($orphans)) {
      $tree = $orphans + $tree;
    }

    return $tree;

  }

  /**
   * Walk the tree and register comments in the corresponding collections.
   *
   * @param   array    $nodes
   * @param   Comment  $parent
   */
  protected function walk(&$nodes, $parent = null) {

    $collection = $parent instanceof Comment ? $parent->children() : $this;

    foreach ($nodes as $entry) {
      $node = $entry['node'];
      $children = &$entry['children'];

      $node->parent($parent);
      $collection->set( $node->id(), $node );

      if (!empty($children)) {
        $this->walk( $children, $node );
      }
    }

  }

  /**
   * Convert the collection to a plain array.
   *
   * @return  array
   */
  public function toArray($callback = NULL) {

    if(is_null($callback)) {
      return $this->data;
    }
    return array_map($callback, $this->data);

  }

  /**
   * Convert the model instance to JSON.
   *
   * @param   integer  $options
   * @return  string
   */
  public function toJson($options = 0) {
    return json_encode($this->toArray(), $options);
  }

  /**
   * Convert the model to its string representation.
   *
   * @return  string
   */
  public function toString() {
    return (string)$this;
  }

  /**
   * Makes it possible to echo the entire object
   *
   * @return string
   */
  public function __toString() {
    return $this->toJson();
  }

}
