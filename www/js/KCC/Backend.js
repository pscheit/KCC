define(['jquery', './CountedProduct', './Product', 'Psc/AjaxHandler', 'Psc/Request'], function ($, KCCCountedProduct, KCCProduct) {

  return function () {
    var that = this;
    this.countedProductsByDay = {};
    this.ajax = new Psc.AjaxHandler();

    this.retrieveCountedProducts = function(date) {
      var d = $.Deferred(), day = date.format('$yy-mm-dd');

      if (that.countedProductsByDay[day]) {
        return that.countedProductsByDay[day];
      }

      that.ajax.handle(
        new Psc.Request({
          url: '/entities/products/counted',
          body: {
            user: 'p.scheit@ps-webforge.com',
            day: day
          },
          method: 'GET'
        })
      ).done(function (response) {
        var countedProductsByDay = response.getBody().countedProductsByDay;
        var retrievedProducts = countedProductsByDay[day] || [];
        var countedProducts = [];

        for (var cp = 0; cp < retrievedProducts.length; cp++) {
          countedProducts.push(new KCCCountedProduct(retrievedProducts[cp]));
        }

        that.countedProductsByDay[day] = countedProducts;

        d.resolve(countedProducts);

      }).fail(function (response) {
        alert("loading failed");
        console.log(response);
      });

      return d.promise();
    };

    this.insertProduct = function(product) {
      var d = $.Deferred(), that = this;

      that.ajax.handle(new Psc.Request({
        url: '/entities/products/',
        method: 'POST',
        body: {
          label: product.label(),
          manufacturer: product.manufacturer(),
          reference: product.reference(),
          unit: product.unit(),
          kcal : product.kcal()
        }
      })).done(function (ajaxResponse) {
        var product = new KCCProduct(that, ajaxResponse.getBody());
        console.log(product);

        d.resolve(product);

      }).fail(function () {
        alert("request failed");
        console.log(arguments);
      });

      return d.promise();
    };

    this.save = function(main) {
      var d = $.Deferred(), that = this;

      var request = new Psc.Request({
        url: '/entities/products/counted',
        body: {
          countedProductsByDay: this.exportCountedProducts(),
          user: 'p.scheit@ps-webforge.com'
        },
        method: 'POST'
      });

      request.sendAs('json');

      that.ajax.handle(request).done(function (response) {
        d.resolve(response);
      }).fail(function (response) {
        alert("saving failed");
        console.log(request, response);
      });

      return d.promise();
    };

    this.exportCountedProducts = function () {
      var data = {}, countedProduct;

      for (var day in that.countedProductsByDay) {
        data[day] = [];
        for (var p in that.countedProductsByDay[day]) {
          countedProduct = that.countedProductsByDay[day][p];

          data[day].push(countedProduct.toJS());
        }
      }

      return data;
    };
  };
});