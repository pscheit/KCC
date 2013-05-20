define(['knockout'], function (ko) {

  return function(backend, properties) {
    var that = this;
    properties = properties || {};

    this.id = ko.observable(properties.id);
    this.label = ko.observable(properties.label);
    this.manufacturer = ko.observable(properties.manufacturer);
    this.reference = ko.observable(properties.reference);
    this.unit = ko.observable(properties.unit);
    this.kcal = ko.observable(properties.kcal);
  };
});
