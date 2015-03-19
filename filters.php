<?php

/**
 * Plugin Filters
 *
 * Allows to register filters that can be used to run application logic just
 * before a route action is executed.
 *
 * @see  CommentPlugin::routes()
 * @var  array
 */

return array(

  /**
   * Route filter that ensures only logged in users have access to a resource.
   */
  'auth' => function() {

    if (!user::current() || !user::current()->isAdmin()) {
      redirect::to('plugin/comments/wizard');
    }

  },

  /**
   * Route filter to check for the installation status of the plugin.
   */
  'installed' => function() {

    if($this->isInstalled()) {
      redirect::home();
    }

  },

  /**
   * Route filter to check if the currently logged-in user is allowed to create
   * comments.
   *
   * @return  boolean
   */
  'userCanCreate' => function() {

    $route = plugin('comments')->route();
    $hash = a::first($route->arguments());
    $page = site()->index()->findBy('hash', $hash);

    return ( $page instanceof Page ) && $page->isVisible();

  },

  /**
   * Route filter to check if the currently logged-in user is allowed to read
   * comments.
   *
   * @return  boolean
   */
  'userCanRead' => function() {

    $route = plugin('comments')->route();
    $hash = a::first($route->arguments());
    $page = site()->index()->findBy('hash', $hash);

    return ( $page instanceof Page ) && $page->isVisible();

  },

  /**
   * Route filter to check if the currently logged-in user is allowed to update
   * comments.
   *
   * @return  boolean
   */
  'userCanUpdate' => function() {

    $route = plugin('comments')->route();
    $id = a::last($route->arguments());
    $comment = comment::find($id);

    return ( $comment instanceof Comment ) && $comment->currentUserCan('update');

  },

  /**
   * Route filter to check if the currently logged-in user is allowed to delete
   * comments.
   *
   * @param   Obj      $route
   * @return  boolean
   */
  'userCanDelete' => function() {

    $route = plugin('comments')->route();
    $id = a::last($route->arguments());
    $comment = comment::find($id);

    return ( $comment instanceof Comment ) && $comment->currentUserCan('delete');

  }

);
