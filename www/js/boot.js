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

define(['require', 'boot-helper'], function (require, boot) {

  return {};
});