<?php

/**
 * Plugin Routes
 *
 * Registry of routes handled by the comments plugin.
 *
 * @see  CommentPlugin::routes()
 * @var  array
 */

return array(

  // CRUD operations

  array(
    'pattern' => 'api/pages/(:any)/comments',
    'action'  => 'CommentController::index',
    'method'  => 'GET',
    'filter'  => 'userCanRead',
  ),

  array(
    'pattern' => 'api/pages/(:any)/comments/(:num)',
    'action'  => 'CommentController::show',
    'method'  => 'GET',
    'filter'  => 'userCanRead',
  ),

  array(
    'pattern' => 'api/pages/(:any)/comments',
    'action'  => 'CommentController::create',
    'method'  => 'POST',
    'filter'  => 'userCanCreate',
  ),

  array(
    'pattern' => 'api/pages/(:any)/comments/(:num)',
    'action'  => 'CommentController::update',
    'method'  => 'PUT|POST',
    'filter'  => 'userCanUpdate',
  ),

  array(
    'pattern' => 'api/pages/(:any)/comments/(:num)',
    'action'  => 'CommentController::delete',
    'method'  => 'DELETE',
    'filter'  => 'userCanDelete',
  ),

  // Form actions

  array(
    'pattern' => 'api/pages/(:any)/comments/create',
    'action'  => 'CommentController::create',
    'method'  => 'POST',
    'filter'  => 'userCanCreate',
  ),

  array(
    'pattern' => 'api/pages/(:any)/comments/delete/(:num)',
    'action'  => 'CommentController::delete',
    'method'  => 'GET',
    'filter'  => 'userCanDelete',
  ),

  // Plugin Installation

  array(
    'pattern' => 'plugins/comments/install/(:num?)',
    'action'  => 'InstallationController::index',
    'method'  => 'GET|POST',
  ),

  array(
    'pattern' => 'plugins/comments/assets/(:all)',
    'action'  => 'InstallationController::assets',
    'method'  => 'GET',
  ),

  // Utility Routes

  array(
    'pattern' => 'api/pages/hash/(:all)',
    'action'  => 'PageController::hash',
    'method'  => 'GET'
  ),

);
