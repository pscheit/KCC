<?php

use Psc\JS\Helper as js;
use Psc\HTML\Page5;

$page = new Page5;
$page->addRequireJS();
$page->addTwitterBootstrapCSS();
$page->loadCSS('css/kcc.css');
$page->setOpen();

print $page;
?>

<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="/">Zähl Dich fit</a>
      <div class="nav-collapse collapse">
        <ul class="nav">
          <li class=""><a href="/">Eingabe</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>


<div class="container">
  <div class="row well">
    <div class="span4">
      <button type="button" class="btn btn-primary" data-bind="click: save" data-loading-text="wird gespeichert...">Speichern</button>
    </div>
    <div class="span3">
      <h4 data-bind="date: {format: '$DD dd. MM yy', value: $data.date}"></h4>
    </div>
    <div class="span2 offset2">
      <div class="btn-group">
        <button type="button" class="btn" data-bind="click: function () { changeView({days: -1}) }">voriger Tag</button>
        <button type="button" class="btn" data-bind="click: function () { changeView({days: +1}) }">nächster Tag</button>
      </div>
    </div>
  </div>

  <form class="">
    <div class="row">
      <div class="span6">
        <input type="text" name="search" id="search" placeholder="Produkt suchen" class="input-xxlarge" />
      </div>
      <!--
      <div class="span2">
        <div class="btn-group">
          <button class="btn">in allen suchen</button>
          <button class="btn dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#">in zuletzt verwendet suchen</a></li>
            <li><a href="#">in allen suchen</a></li>
          </ul>
        </div>
      </div>
      -->
    </div>
  </form>

  <div class="row">
    <div class="span12">
      <table class="table table-striped">
        <thead>
          <th>Bezeichnung</th>
          <th>Menge</th>
          <th>kcal</th>
          <th></th>
        </thead>
        <tfoot>
          <tr>
            <td></td>
            <td><strong>Summe</strong></td>
            <td><strong data-bind="text: kcals()"></strong></td>
            <td></td>
        </tfoot>
        <tbody data-bind="foreach: countedProducts">
          <tr>
            <td data-bind="text: label"></td>
            <td>
              <input type="text" class="input-mini" data-bind="value: amount">
              <span class="unit" data-bind="text: unit"></span>
            </td>
            <td data-bind="text: kcals"></td>
            <td><button type="button" data-bind="click: $parent.removeCountedProduct" class="btn btn-small btn-danger"><i class="icon-trash"></i></button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="row" style="margin-top: 20px" data-bind="with: newProduct">
    <div class="span5 well">
      <form class="form-horizontal">
        <fieldset>
          <legend>neues Produkt eintragen</legend>
          <div class="control-group">
            <label class="control-label" for="inputName">Name</label>
            <div class="controls">
              <input type="text" name="label" data-bind="value: label" id="inputName">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputManufacturer">Hersteller</label>
            <div class="controls">
              <input type="text" id="inputManufacturer" name="manufacturer" data-bind="value: manufacturer">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputReference">Mengen Referenz</label>
            <div class="controls">
              <input type="text" class="input-small" id="inputReference" name="reference" placeholder="Menge" data-bind="value: reference" />
              <input type="text" class="input-mini" id="inputUnit" name="unit" placeholder="Einheit" data-bind="value: unit" />
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputKcal">kcal</label>
            <div class="controls">
              <input type="text" class="input-mini" id="inputKcal" name="kcal" data-bind="value: kcal">
            </div>
          </div>
          <div class="control-group">
            <div class="controls">
              <button type="button" data-bind="click: insert" class="btn">Eintragen</button>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
  require(['boot'], function (boot) {
    require(['jquery', 'knockout', 'hogan', 'twitter-bootstrap', 'twitter-typeahead', 'Psc/Date', 'Psc/AjaxHandler', 'Psc/Request', 'jquery-ui-i18n'], function ($, ko, hogan) {
      $.datepicker.setDefaults($.datepicker.regional['de']);

      var $search = $('#search');

      var products = [
        {
          value: 1,
          tokens: ["Beeren", "Müsli"],
          label: "Beeren-Müsli",
          kcal: 137,
          unit: 'g'
        },
        {
          value: 2,
          tokens: ["Nutella"],
          label: "Nutella",
          kcal: 450,
          unit: 'g'
        },
        {
          value: 3,
          tokens: ["Ei", "Eier"],
          label: "Ei",
          kcal: 80,
          reference: 1,
          unit: 'stk'
        },
        {
          value: 4,
          tokens: ["Vollmilch", "Milch", "3,8%"],
          label: "Vollmilch 3,8%",
          kcal: 250,
          unit: 'ml'
        }
      ];

      $search.typeahead({
        name: 'products',
        local: products,
        template: "<p>{{label}}</p>",
        engine: hogan
      });
      

      var CountedProduct = function (properties) {
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
        };

        this.kcals = ko.computed(function() {
          return this.amount() * this.kcal / this.getReference();
        }, this);

        this.formattedAmount = ko.computed(function () {
          return this.amount()+' '+this.unit;
        }, this);

      };

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

      var KCCBackend = function () {
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
              countedProducts.push(new CountedProduct({
                value: 1,
                label: "Beeren-Müsli",
                tokens: ["Beeren", "Müsli"],
                amount: 130,
                unit: "g",
                kcal: 137
              }));

              countedProducts.push(new CountedProduct({
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
        }

        this.registerCountedProducts = function(countedProducts, date) {
          var day = date.format('$yy-mm-dd');

          return that.countedProductsByDay[day] = countedProducts;
        };

        this.insertProduct = function(product) {
          that.ajax.handle(new Psc.Request({
            url: '/entities/products/',
            method: 'POST',
            body: product
          })).done(function () {
            console.log(arguments);
          }).fail(function () {
            alert("request failed");
            console.log(arguments);
          });
        }
      };

      var KCCProductModel = function(backend) {
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

      var KCCModel = function(backend, date) {
        var that = this;

        this.backend = backend;
        this.date = ko.observable(date);
        this.countedProducts = ko.observableArray();
        this.newProduct = new KCCProductModel(this.backend);

        this.addCountedProduct = function (countedProduct) {
          that.countedProducts.push(countedProduct);
        };

        this.removeCountedProduct = function (countedProduct) {
          that.countedProducts.remove(countedProduct);
        }

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
            $btn.button('reset')
          }, 3000);

        };

        this.changeView = function(change) {
          that.view(that.date().add(change));
        }

        this.view = function (date) {
          $.when(that.backend.retrieveCountedProducts(date)).then(function (countedProducts) {
            that.date(date);

            that.countedProducts(countedProducts);
          });
        };

        // init
        this.view(date);
      };
      
      var main = new KCCModel(new KCCBackend(), new Psc.Date());
      ko.applyBindings(main);

      $search.on('typeahead:autocompleted typeahead:selected', function (e, datum) {
        e.preventDefault();
        var countedProduct = new CountedProduct($.extend({}, datum));

        main.addCountedProduct(countedProduct);
      });
    });
  });
</script>
<?php print $page->getClose(); ?>