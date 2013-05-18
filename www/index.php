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
    require(
      [
        'jquery', 'knockout', 'hogan', 
        'app/KCC/Main', 'app/KCC/Backend', 'app/KCC/CountedProduct',
        'app/date-binding', 'twitter-bootstrap', 'twitter-typeahead', 'Psc/Date', 'jquery-ui-i18n'
      ], function (
        $, ko, hogan, 
        KCCMain, KCCBackend, KCCCountedProduct
      )
    {

      $.datepicker.setDefaults($.datepicker.regional['de']);

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


      var main = new KCCMain(new KCCBackend(), new Psc.Date());
      ko.applyBindings(main);

      var $search = $('#search');
      $search.typeahead({
        name: 'products',
        local: products,
        template: "<p>{{label}}</p>",
        engine: hogan
      });

      $search.on('typeahead:autocompleted typeahead:selected', function (e, datum) {
        e.preventDefault();
        var countedProduct = new KCCCountedProduct($.extend({}, datum));

        main.addCountedProduct(countedProduct);
      });
      
    });
  });
</script>

<?php print $page->getClose(); ?>