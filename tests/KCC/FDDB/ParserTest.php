<?php

namespace KCC\FDDB;

class ParserTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'KCC\\FDDB\\Parser';
    parent::setUp();

    $this->parser = new Parser();
  }

  public function testSearchResultParsing() {
    $values = array(
      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/masterfoods_balisto_joghurt-beeren-mix/index.html',
        'title'=>'Balisto, Joghurt-Beeren-Mix',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/rewe_beerenmischung_heidelbeeren_johannisbeeren_himbeeren_brombee/index.html',
        'title'=>'Beerenmischung, Heidelbeeren, Johannisbeeren, Himbeeren, Brombee',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/alnatura_beeren-muesli/index.html',
        'title'=>'Beeren-Müsli',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/linessa_vital_und_active_linessa_rote_gruetze_light_beeren/index.html',
        'title'=>'Linessa Rote Grütze light, Beeren',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/gut_und_guenstig_beeren_mischung_erdbeere_kirsch_himmbeer_brombeere/index.html',
        'title'=>'Beeren Mischung, Erdbeere, Kirsch, Himmbeer, Brombeere....',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/diverse_kaesekuchen_mit_gemischten_beeren/index.html',
        'title'=>'Käsekuchen, mit gemischten Beeren',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/gartenkrone_beerenmischung/index.html',
        'title'=>'Beerenmischung',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/naturprodukt_johannisbeeren_schwarz/index.html',
        'title'=>'Johannisbeeren, schwarz',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/edeka_bio_wertkost_dinkelmuesli_vollkorn_mit_fruechte_und_beeren/index.html',
        'title'=>'Bio Wertkost Dinkelmüsli, Vollkorn mit Früchte & Beeren',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/diverse_holundersirup_beeren_holunder/index.html',
        'title'=>'Holundersirup Beeren, Holunder',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/vita_shop_goji_beeren_getrocknet/index.html',
        'title'=>'Goji Beeren getrocknet',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/layenberger_fit_plus_feelgood_schlank_diaet_rote_beeren-joghurt/index.html',
        'title'=>'Fit + Feelgood Schlank Diät, Rote Beeren-Joghurt',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/juetro_beeren-mix_tiefgefroren/index.html',
        'title'=>'Beeren-Mix, tiefgefroren',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/gut_und_guenstig_beeren_mischung_mit_sauerkirschen/index.html',
        'title'=>'Beeren Mischung mit Sauerkirschen',
      ),

      (object) array(
        'link' => 'http://fddb.info/db/de/lebensmittel/rewe_rewe_bio_muesli_beeren/index.html',
        'title'=>'Rewe Bio Müsli Beeren'
      )      
    );

    $products = $this->parser->parseSearchResult($this->getSearchFile('beeren')->getContents());

    $this->assertEquals($values, $products);
  }

  protected function getSearchFile($name) {
    return $this->getTestDirectory()->sub('searches')->getFile($name.'.html');
  }
}