define(['jquery', 'knockout'], function($, ko) {
  return function (properties) {
    var that = this;

    that.amount = ko.observable();

    for (var key in properties) {
      if (key === 'amount') {
        this[key](properties[key]);
      } else {
        this[key] = properties[key];
      }
    }

    this.getReference = function () {
      if (that.reference !== undefined) {
        return that.reference;
      }

      return 100;
    };

    if (that.amount() === undefined) {
      that.amount(this.getReference());
    }    

    this.kcals = ko.computed(function() {
      return this.amount() * this.kcal / this.getReference();
    }, this);

    this.formattedAmount = ko.computed(function () {
      return this.amount()+' '+this.unit;
    }, this);

    this.toJS = function () {
      return {
        amount: this.amount(),
        productId: this.productId
      };
    };
  };
});