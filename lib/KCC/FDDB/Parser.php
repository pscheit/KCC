<?php

namespace KCC\FDDB;

use Psc\JS\jQuery;
use Psc\XML\Scraper;

class Parser {

  protected $scraper;

  public function __construct() {
    $this->scraper = new Scraper();
  }

  public function parseSearchResult($html) {
    $mainblock = new jQuery('#content .mainblock', $html);

    if (count($mainblock) !== 1) {
      throw new ParsingException($mainblock->getSelector().' cannot be found.');
    }

    $table = $mainblock->find('.leftblock table:eq(1)');

    $stop = $start = FALSE;
    $scraped = $this->scraper
      ->table($table)
      ->dontParseHeader()
      ->rowFilter(function ($row, $headerFound) use (&$stop, &$start) {
        if (count($row->find('td')) != 2) return FALSE;

        if (!$start && mb_stripos($row->find('td:eq(0)')->text(), 'Beliebte Produkte') !== FALSE) {
          $start = TRUE;
        }

        if (!$stop && mb_stripos($row->find('td:eq(0)')->text(), 'Normale Produkte') !== FALSE) {
          $stop = TRUE;
        }

        if (count($row->find('td:eq(1) a')) === 0) return FALSE;

        return $start && !$stop;
      })
      ->tdConverter(function ($td, $key) {
        if ($key == 0) {
          return NULL;
        }

        $a = $td->find('a:eq(0)');

        return (object) array(
          'title'=>$a->text(),
          'link'=>$a->attr('href')
        );
      })
      ->scrape();

    $values = array();
    foreach ($scraped->rows as $row) {
      if (count($row) == 2) {
        $values[] = $row[1];
      }
    }

    return $values;
  }
}
