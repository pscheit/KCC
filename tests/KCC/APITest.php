<?php

namespace KCC;

use KCC\Entities\Product;
use KCC\Entities\User;
use KCC\Entities\CountedProduct;
use Webforge\Common\DateTime\Date;

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
    $this->resetDatabaseOnNextTest();
    $this->insertSomeProducts();
    $products = $this->test->acceptance('product')->dispatch('GET', '/entities/products', array(), 'json', 200, $public = TRUE);

    $this->assertCount(4, $products);

    foreach ($products as $product) {
      $debug = print_r($product, TRUE);
      $this->assertObjectHasAttribute('label', $product, $debug);
      $this->assertObjectHasAttribute('tokens', $product, $debug);
    }
  }

  public function testSavesCountedProductsByDate() {
    $this->resetDatabaseOnNextTest();
    $this->insertUser('p.scheit');
    $this->insertSomeProducts();

    $body = json_decode('{
      "countedProductsByDay": {"2013-05-19":[{"amount":"130","productId":"1"},{"amount":"120","productId":"3"}]},
      "user": "p.scheit@ps-webforge.com"
     }');

    $this->test->acceptance('product')->dispatch('POST', '/entities/products/counted', $body, 'json', 200, TRUE);
    $this->em->clear();

    $products = $this->em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery('p.scheit@ps-webforge.com', new Date('2013-05-19'))->getResult();

    $this->assertCount(2, $products);
    $this->assertContainsOnlyInstancesOf('KCC\Entities\CountedProduct', $products);

    foreach ($products as $countedProduct) {
      $this->assertEquals('2013-05-19', $countedProduct->getDay()->format('Y-m-d'));
      $this->assertGreaterThan(0, $countedProduct->getAmount());
    }
  }

  public function testSavesCountedProductsByDateNotTwice() {
    $this->resetDatabaseOnNextTest();
    $this->insertUser('p.scheit');
    $this->insertSomeProducts();

    $body = json_decode('{
      "countedProductsByDay": {"2013-05-19":[{"amount":"130","productId":"1"},{"amount":"120","productId":"3"}]},
      "user": "p.scheit@ps-webforge.com"
     }');

    $this->test->acceptance('product')->dispatch('POST', '/entities/products/counted', $body, 'json', 200, TRUE);
    $this->em->clear();
    $products = $this->em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery('p.scheit@ps-webforge.com', new Date('2013-05-19'))->getResult();
    $this->assertCount(2, $products);

    $this->test->acceptance('product')->dispatch('POST', '/entities/products/counted', $body, 'json', 200, TRUE);
    $this->em->clear();
    $products = $this->em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery('p.scheit@ps-webforge.com', new Date('2013-05-19'))->getResult();
    $this->assertCount(2, $products);
  }

  public function testGetsAllCountedProductsForADayGroupedByDay() {
    $this->resetDatabaseOnNextTest();
    $this->insertUser('p.scheit');
    $this->insertSomeProducts();

    $body = json_decode('{
      "countedProductsByDay": {"2013-05-19":[{"amount":"130","productId":"1"},{"amount":"120","productId":"3"}]},
      "user": "p.scheit@ps-webforge.com"
     }');
    $this->test->acceptance('product')->dispatch('POST', '/entities/products/counted', $body, 'json', 200, TRUE);

    $structure = $this->test->acceptance('product')->dispatch('GET', '/entities/products/counted', array('user'=>'p.scheit@ps-webforge.com', 'day'=>'2013-05-19'), 'json', 200, TRUE);
    $this->assertEquals($body, $structure);
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

  public function insertUser($name) {
    $user = new User($name.'@ps-webforge.com');
    $user->hashPassword('hae');
    $this->em->persist($user);

    return $user;
  }
}