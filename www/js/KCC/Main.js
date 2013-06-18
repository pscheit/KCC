define(['jquery', 'app/boot', 'knockout', './Product', './CountedProduct', 'app/persona', 'twitter-bootstrap'], function($, boot, ko, KCCProduct, KCCCountedProduct, Persona) {

  return function(backend, date) {
    var that = this;

    this.backend = backend;
    this.date = ko.observable(date);
    this.countedProducts = ko.observableArray();
    this.newProduct = new KCCProduct(this.backend);

    this.addCountedProduct = function(countedProduct) {
      that.countedProducts.push(countedProduct);
    };

    this.removeCountedProduct = function(countedProduct) {
      that.countedProducts.remove(countedProduct);
    };

    this.kcals = ko.computed(function() {
      var sum = 0;
      var products = that.countedProducts();

      for (var key in products) {
        sum += products[key].kcals();
      }

      return sum;
    });

    this.save = function(kcc, event) {
      var $btn = $(event.target);

      $btn.button('loading');

      that.backend.save(that).done(function() {

      }).always(function() {
        $btn.button('reset');
      });
    };

    this.insertProduct = function() {
      that.backend.insertProduct(that.newProduct).done(function(product) {
        that.addCountedProduct(
          that.createCountedProductFromProduct(ko.toJS(product)));
      });
    };

    this.createCountedProductFromProduct = function(productProperties) {
      var properties = $.extend({
        productId: productProperties.id
      }, productProperties);
      delete properties.id;

      return new KCCCountedProduct(properties);
    };

    this.changeView = function(change) {
      that.view(that.date().add(change));
    };

    this.view = function(date) {
      $.when(that.backend.retrieveCountedProducts(date)).then(function(countedProducts) {
        that.date(date);

        that.countedProducts(countedProducts);
      });
    };

    var PersonaView = function(persona) {
      var that = this;

      this.email = ko.observable();

      this.isLoggedIn = ko.computed(function() {
        return that.email() !== undefined;
      });

      this.signIn = function() {
        persona.login();
      };

      this.signOut = function() {
        persona.logout();
      };

      this.onSignIn = function(user) {
        that.email(user.email);
      };

      this.onSignOut = function () {
        that.email(undefined);
      };

      persona.init(this.onSignIn, this.onSignOut);
    };

    this.persona = new PersonaView(new Persona());

    // init
    this.view(date);
  };
});