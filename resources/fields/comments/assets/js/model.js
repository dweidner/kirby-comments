(function(global, $, app) {

  'use strict';

  /**
   * Inform the user about errors during processing.
   *
   * @param   {string}  msg  Message to write.
   */
  var alert = function(msg) {

    var form = $('.mainbar form:first');
    var container = form.find('.message-list');

    if (container.length === 0) {
      container = $('<div/>').addClass('message-list');
      form.prepend(container);
    }

    container.message('alert', msg);

  };

  /**
   * Constructor
   *
   * Creates a new comment instance.
   *
   * @param {object}  attrs  Comment attributes.
   */
  function Comment(attrs) {

    for (var p in attrs) {
      this[p] = attrs[p];
    }

  }

  // Comment prototype
  Comment.prototype = {

    /**
     * Test if the comment is already approved by a moderator.
     *
     * @return  {boolean}
     */
    isApproved: function() {
      return !!this.status;
    }

  };

  /**
   * Load comments from the API endpoint via the id.
   *
   * @param   {string}   hash
   * @param   {integer}  id
   *
   * @return  {Comment}
   */
  Comment.load = function(hash, id) {

    var deferred = $.Deferred();

    $.get('/api/pages/' + hash + '/comments/' + id)

      .done(function(response) {
        deferred.resolve(new Comment(response.data.comment));
      })

      .fail(function() {
        alert('Comment not found');
        deferred.reject();
      });

    return deferred;

  };

  // Export as global variable
  global.CommentModel = Comment;

})(window, window.jQuery, window.app);
