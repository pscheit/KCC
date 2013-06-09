/*globals navigator */
define(['jquery', 'Psc/AjaxHandler', 'Psc/Request', 'https://login.persona.org/include.js'], function ($) {
  var ajax = new Psc.AjaxHandler();
  var user = null;

  return {
    init: function () {
      var d = $.Deferred();

      navigator.id.watch({
        loggedInUser: user,

        onlogin: function (assertion) {
          ajax.handle(
            new Psc.Request({
              method: 'POST',
              url: '/kcc/auth/login',
              body: {
                assertion: assertion
              }
            })
          ).done(function (response) {
            user = response.getBody().email;

            d.resolve(response.getBody());

          }).fail(function (response) {
            navigator.id.logout();

            d.reject(response.getBody());
          });
        },
        onlogout: function () {
          user = null;
        }
      });

      return d.promise();
    },
    setUser: function(email) {
      user = email;
    },

    getUser: function() {
      return user;
    },

    logout: function () {
      navigator.id.logout();
    },

    login: function () {
      navigator.id.request();
    }
  };
});