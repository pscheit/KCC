define(['jquery', 'app/boot', 'knockout', './Product', './CountedProduct'], function ($, boot, ko, KCCProduct, KCCCountedProduct) {

  return function(backend, date) {
    var that = this;

    this.backend = backend;
    this.date = ko.observable(date);
    this.countedProducts = ko.observableArray();
    this.newProduct = new KCCProduct(this.backend);

    this.addCountedProduct = function (countedProduct) {
      that.countedProducts.push(countedProduct);
    };

    this.removeCountedProduct = function (countedProduct) {
      that.countedProducts.remove(countedProduct);
    };

    this.kcals = ko.computed(function () {
      var sum = 0;
      var products = that.countedProducts();

      for (var key in products) {
        sum += products[key].kcals();
      }

      return sum;
    });

    this.save = function (kcc, event) {
      var $btn = $(event.target);

      $btn.button('loading');

      setTimeout(function () {
        $btn.button('reset');
      }, 3000);

    };

    this.insertProduct = function () {
      that.backend.insertProduct(that.newProduct).done(function (product) {
        var countedProduct = new KCCCountedProduct($.extend({}, ko.toJS(product)));

        that.addCountedProduct(countedProduct);
      });
    };

    this.changeView = function(change) {
      that.view(that.date().add(change));
    };

    this.view = function (date) {
      $.when(that.backend.retrieveCountedProducts(date)).then(function (countedProducts) {
        that.date(date);

        that.countedProducts(countedProducts);
      });
    };

    // init
    this.view(date);
  };
});