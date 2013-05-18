<?php

namespace KCC;

use KCC\Entities\Product;

class APITest extends AcceptanceTestCase {

  public function testInsertsAProduct() {
    $this->resetDatabaseOnNextTest();
    $product = $this->dispatchRequest('POST', '/entities/products', json_decode('{
      "label":"Würstchen",
      "manufacturer": "ALDI",
      "reference":100,
      "unit":"g",
      "kcal":400
    }'))->getBody();

    $this->em->clear();
    $product = $this->hydrate('product', $product->getIdentifier());

    $this->assertEquals('Würstchen', $product->getLabel());
    $this->assertEquals('ALDI', $product->getManufacturer());
    $this->assertEquals(100.00, $product->getReference());
    $this->assertEquals('g', $product->getUnit());
    $this->assertEquals(400, $product->getKcal());
    $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $product->getCreated());
  }

  public function testGetsAllProducts() {
    $this->resetDatabaseOnNextTest();
    $this->insertSomeProducts();

    $products = $this->dispatchRequest('GET', '/entities/products')->getBody();

    $this->assertCount(4, $products);
  }

  public function testGetsAllProductsJSONResponse() {
    $this->insertSomeProducts();
    $products = $this->test->acceptance('product')->dispatch('GET', '/entities/products', array(), 'json', 200, $public = TRUE);

    $this->assertCount(4, $products);

    foreach ($products as $product) {
      $debug = print_r($product, TRUE);
      $this->assertObjectHasAttribute('label', $product, $debug);
      $this->assertObjectHasAttribute('tokens', $product, $debug);
    }
  }

  public function insertSomeProducts() {
    $em = $this->em;
    $insert = function ($label, $manufacturer, $reference, $unit, $kcal) use ($em) {
      $product = new Product($label, $manufacturer, $reference, $unit, $kcal);
      $em->persist($product);

      return $product;
    };

    $insert('Würstchen', 'ALDI', 100, 'g', 400);
    $insert('Nutella', 'Ferrero', 100, 'g', 600);
    $insert('Karotten', NULL, 100, 'g', 600);
    $insert('Becel', NULL, 100, 'g', 283);

    $em->flush();
    $em->clear();
  }
}