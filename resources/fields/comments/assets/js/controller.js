(function(global, $, Handlebars, app, Comment) {

  'use strict';

  /**
   * Compiled handlebars form template.
   *
   * @type  {Function?}
   */
  var template = null;

  /**
   * Render the edit comment form in a modal window.
   *
   * @param  {string}   page     Page hash.
   * @param  {Comment}  comment  Comment to display.
   */
  var openForm = function(page, comment) {

    if (!template) {
      template = Handlebars.compile($('.comment-form-template').html());
    }

    app.modal.html(template({
      hash: page,
      comment: comment
    }));
    app.modal.show();
    app.modal.find('.modal-content').on('click', function(e) {
      e.stopPropagation();
    });
    app.modal.find('[autofocus]').focus();

  };

  /**
   * Comments Controller
   *
   * @type  {Object}
   */
  var controller = {

    /**
     * Edit an individual comment.
     *
     * @param   {string}  hash  Page hash.
     * @param   {integer} id    Comment id.
     */
    edit: function(hash, id) {

      Comment.load(hash, id).then(function(comment) {
        openForm(hash, comment);
      });

    }

  };

  // Export as global variable
  global.CommentsController = controller;

})(window, window.jQuery, window.Handlebars, window.app, window.CommentModel);
