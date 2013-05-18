/*globals requirejs*/
requirejs.config({
  baseUrl: "/psc-cms-js/lib",

  paths: {
    app: '/js'
  },

  map: {
    '*': {
      "app/boot": "boot"
    }
  }
});

define(['require', 'path-config'], function (require) {

  //require(['app/main']);

  return {};
});