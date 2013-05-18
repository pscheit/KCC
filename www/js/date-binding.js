define(['knockout', 'jquery'], function (ko, $) {
  ko.bindingHandlers.date = {
    init: function (element, valueAccessor) {
      var settings = valueAccessor();

      $(element).html(settings.value().format(settings.format));
    },
    update: function (element, valueAccessor) {
      var settings = valueAccessor();

      $(element).html(settings.value().format(settings.format));
    }
  };
});