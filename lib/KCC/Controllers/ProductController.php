<?php

namespace KCC\Controllers;

use KCC\Entities\Product;

class ProductController extends \Psc\CMS\Controller\AbstractEntityController {
  
  protected function setUp() {
    parent::setUp();

    $this->addBlacklistProperty('created');
    $this->addOptionalProperty('updated');
    $this->addOptionalProperty('manufacturer');
  }
  
  public function getEntityName() {
    return 'KCC\Entities\Product';
  }
}
?>