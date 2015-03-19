(function(global, $, routie, app, controller) {

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
   * Custom panel field.
   *
   * @type  {Object}
   */
  var field = {

    /**
     * Field type.
     *
     * @type  {string}
     */
    type: 'Comments',

    /**
     * Version of the field plugin.
     *
     * @type  {string}
     */
    version: '2.x-0.1-alpha',

    /**
     * Initialize field plugin.
     *
     * @return  {self}
     */
    init: function() {
      this.routes();
      return this;
    },

    /**
     * Initialize field routes.
     *
     * @return  {object}
     */
    routes: function() {

      // Register custom field routes
      this.routes = {
        '/pages/show/*/c:*/action:*/*?': $.proxy(this.handleRoute, this),
      };
      global.routes = $.extend(this.routes, global.routes || {});

      // Flush and reload application routes
      setTimeout(function() {
        routie.removeAll();
        routie(global.routes);
      }, 250);

    },

    /**
     * Retrieve the hash value for a page object.
     *
     * @param   {string}  page  Page uri.
     * @return  {string}
     */
    getHash: function(page) {

      var deferred = $.Deferred();

      var notFound = function() {
        alert('Page not found');
        deferred.reject();
      };

      $.get('/api/pages/hash/' + page).fail(notFound).done(function(response) {
        var hash = response.data && response.data.hash ? response.data.hash : false;
        if (hash) {
          deferred.resolve(hash);
        } else {
          notFound();
        }
      });

      return deferred;

    },

    /**
     * Route callback.
     *
     * @param   {string}  uri
     * @param   {integer} id
     * @param   {string}  action
     */
    handleRoute: function(uri, id, action) {

      this.getHash(uri).then(function(hash) {

        switch(action) {
          case 'edit':
            controller.edit(hash, parseInt(id));
            break;
          default:
            app.modal.alert('Unknown action to perform');
            break;
        }

      });

    }

  };

  return field.init();

})(window, window.jQuery, window.routie, window.app, window.CommentsController);
