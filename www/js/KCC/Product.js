define(['knockout'], function (ko) {

  return function(backend) {
    var that = this;

    this.label = ko.observable("Würstchen");
    this.manufacturer = ko.observable("ALDI");
    this.reference = ko.observable(100);
    this.unit = ko.observable('g');
    this.kcal = ko.observable(400);

    this.insert = function (product) {
      backend.insertProduct({
        label: that.label(),
        manufacturer: that.manufacturer(),
        reference: that.reference(),
        unit: that.unit(),
        kcal : that.kcal()
      });
    };
  };
});
