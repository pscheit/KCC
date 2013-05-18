<?php

namespace KCC\Entities;

class ProductTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'KCC\\Entities\\Product';
    parent::setUp();

    $this->product = new Product('Würstchen', 'ALDI', 100, 'g', 400);
  }

  public function testJSONExports() {
    $json = $this->product->export();

    $this->assertAttributeEquals('Würstchen', 'label', $json);
    $this->assertAttributeEquals(400, 'kcal', $json);
    $this->assertAttributeEquals('g', 'unit', $json);
    $this->assertAttributeEquals(100, 'reference', $json);
    $this->assertAttributeEquals(array('Würstchen'), 'tokens', $json);
  }
}