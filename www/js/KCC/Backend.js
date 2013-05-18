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

      window.setTimeout(function () {
        var countedProducts = [];

        if (day === '2013-05-17') {
          countedProducts.push(new KCCCountedProduct({
            value: 1,
            label: "Beeren-Müsli",
            tokens: ["Beeren", "Müsli"],
            amount: 130,
            unit: "g",
            kcal: 137
          }));

          countedProducts.push(new KCCCountedProduct({
            value: 4,
            tokens: ["Vollmilch", "Milch", "3,8%"],
            label: "Vollmilch 3,8%",
            kcal: 250,
            amount: 120,
            unit: 'ml'
          }));
        }

        d.resolve(that.registerCountedProducts(countedProducts, date));
      }, 800);

      return d.promise();
    };

    this.registerCountedProducts = function(countedProducts, date) {
      var day = date.format('$yy-mm-dd');

      return that.countedProductsByDay[day] = countedProducts;
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

        d.resolve(product);

      }).fail(function () {
        alert("request failed");
        console.log(arguments);
      });

      return d.promise();
    };
  };
});