define([
  'jquery', 'knockout', 'hogan',
  'app/KCC/Main', 'app/KCC/Backend', 'app/KCC/CountedProduct',
  'app/date-binding', 'twitter-typeahead', 'Psc/Date', 'jquery-ui-i18n'
  ], function (
    $, ko, hogan,
    KCCMain, KCCBackend, KCCCountedProduct
    )
  {

    $.datepicker.setDefaults($.datepicker.regional['de']);

    var main = new KCCMain(new KCCBackend(), new Psc.Date());
    ko.applyBindings(main);

    var $search = $('#search')
    .typeahead({
      name: 'products',
      remote: '/entities/products',
      template: "<p>{{label}} {{#manufacturer}}({{manufacturer}}){{/manufacturer}}</p>",
      engine: hogan,
      limit: 8,
      valueKey: "id"
    })
    .on('typeahead:autocompleted typeahead:selected', function (e, datum) {
      e.preventDefault();

      main.addCountedProduct(
        main.createCountedProductFromProduct(datum)
        );

      window.setTimeout(function () {
        $search.val('');
      }, 10);
    });
});
