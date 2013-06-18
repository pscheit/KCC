/*globals navigator */
define(['jquery', 'Psc/AjaxHandler', 'Psc/Request', 'https://login.persona.org/include.js'], function ($) {

  return function () {
    var that = this;
    var ajax = new Psc.AjaxHandler();

    // 1st of all we check the api to determine if the user is already authenticated and healthy:
    this.email = null;
    this.loginHooks = $.Callbacks();
    this.logoutHooks = $.Callbacks();

    this.init = function (loggedInHook, loggedOutHook) {
      if (loggedInHook) {
        that.loginHooks.add(loggedInHook);
      }

      if (loggedOutHook) {
        that.logoutHooks.add(loggedOutHook);
      }

      this.whoami().done(function (user) {
        // if yes: user is logged in and we provide that info as parameter to navigator.id.watch()

        if (user.email) {
          that.triggerLogin(user);
          that.triggerEvent("user is initial logged in");
        } else {
          // user it not logged in?
          // do nothing here. its "normal" to be logged out
          that.triggerEvent("user is initial logged out");
        }

        navigator.id.watch({
          loggedInUser: that.email,
          onlogin: that.onLogin,
          onlogout: that.onLogout
        });
      });
    };

    this.onLogin = function (assertion) {
      ajax.handle(
        new Psc.Request({
          method: 'POST',
          url: '/kcc/auth/login',
          body: {
            assertion: assertion
          }
        })
      )
      .done(function (response) {
        var who = response.getBody();
        that.triggerEvent('user is logged in with persona');
        that.triggerLogin(who);
      })
      .fail(function (response) {
        // e.g.: the user has hacked or the email is not verified from backend
        // we could switch with response.code here

        that.triggerEvent('user login not verified by backend');
        navigator.id.logout();
      });
    };

    this.onLogout = function () {
      that.triggerEvent("logged out with persona");
      that.setEmail(null);

      ajax.handle(
        new Psc.Request({
          method: 'POST',
          url: '/kcc/auth/logout',
          body: {}
        })
      )
      .done(function (response) {
        that.triggerEvent('logged out by backend');
        that.triggerLogout();
      })
      .fail(that.handleError);
    };

    this.setEmail = function (email) {
      that.email = email;
    };

    this.whoami = function () {
      var d = $.Deferred();

      ajax.handle(
        new Psc.Request({
          method: 'GET',
          url: '/kcc/auth/whoami'
        })
      )
      .done(function (response) {
        d.resolve(response.getBody());
      })
      .fail(that.handleError);

      return d.promise();
    };

    this.triggerEvent = function (name) {
      console.log('persona-event', name);
    };

    this.triggerLogin = function (user) {
      that.setEmail(user.email);
      that.loginHooks.fire(user);
    };

    this.triggerLogout = function (user) {
      that.setEmail(null);
      that.logoutHooks.fire();
    };

    this.handleError = function (response) {
      console.log(response);
      alert('Programmier Fehler');
    };

    this.logout = function () {
      navigator.id.logout();
    };

    this.login =  function () {
      navigator.id.request();
    };
  };
});