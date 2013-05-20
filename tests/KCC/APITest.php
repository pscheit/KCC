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
    
    $this->assertEquals(json_decode('{
      "countedProductsByDay": {
        "2013-05-19": [
          {
            "amount":"130",
            "productId":"1",
            "label": "Würstchen",
            "reference": 100,
            "unit": "g",
            "kcal": 400
          },
          {
            "amount":"120",
            "productId":"3",
            "label": "Karotten",
            "reference": 100,
            "unit": "g",
            "kcal": 600
          }
        ]
      },
      "user": "p.scheit@ps-webforge.com"
     }'), $structure);
  }

  public function testPersonaAuthLogin() {
    $this->resetDatabaseOnNextTest();
    $this->insertUser('p.scheit');

    $this->em->flush();
    $this->em->clear();

    $response = $this->test->acceptance(NULL)->dispatch('POST', '/cms/persona/verify', (object) array('assertion'=>'eyJhbGciOiJSUzI1NiJ9.eyJwdWJsaWMta2V5Ijp7ImFsZ29yaXRobSI6IkRTIiwieSI6IjJlMTU0YWE1MDlhZjI0N2VhNGNhZmU4Yzg1Y2E5OWRmYWEwMTk4YjVlMmFiZTQzNDI1N2M2YWM3Mjk1MTc5OTIyMjkxZjYzNTA3OGVlYTQxZWE1ODgxMDc3YmFjMTk0MjIyNjMwZjFhNjU1YTM5ZTc2MDRkNmQyMzM2ODVjZDhmYTJmNTAzZjdiNWI2YWI3MDdkZDI0NDY3Yzc0YWU2MjQ5OGRhNDliODhlYjVjMDU5MTI3ZTE3MmU5M2VmMTNiOWU3MzNjOWMwMjNjODM1ZDVkNzM3OTFiYzU2YjNjMjYzYmRjN2JjZDI3ODQyMjgyMGM3YTQ4ZGMwYmM5YzVlNiIsInAiOiJmZjYwMDQ4M2RiNmFiZmM1YjQ1ZWFiNzg1OTRiMzUzM2Q1NTBkOWYxYmYyYTk5MmE3YThkYWE2ZGMzNGY4MDQ1YWQ0ZTZlMGM0MjlkMzM0ZWVlYWFlZmQ3ZTIzZDQ4MTBiZTAwZTRjYzE0OTJjYmEzMjViYTgxZmYyZDVhNWIzMDVhOGQxN2ViM2JmNGEwNmEzNDlkMzkyZTAwZDMyOTc0NGE1MTc5MzgwMzQ0ZTgyYTE4YzQ3OTMzNDM4Zjg5MWUyMmFlZWY4MTJkNjljOGY3NWUzMjZjYjcwZWEwMDBjM2Y3NzZkZmRiZDYwNDYzOGMyZWY3MTdmYzI2ZDAyZTE3IiwicSI6ImUyMWUwNGY5MTFkMWVkNzk5MTAwOGVjYWFiM2JmNzc1OTg0MzA5YzMiLCJnIjoiYzUyYTRhMGZmM2I3ZTYxZmRmMTg2N2NlODQxMzgzNjlhNjE1NGY0YWZhOTI5NjZlM2M4MjdlMjVjZmE2Y2Y1MDhiOTBlNWRlNDE5ZTEzMzdlMDdhMmU5ZTJhM2NkNWRlYTcwNGQxNzVmOGViZjZhZjM5N2Q2OWUxMTBiOTZhZmIxN2M3YTAzMjU5MzI5ZTQ4MjliMGQwM2JiYzc4OTZiMTViNGFkZTUzZTEzMDg1OGNjMzRkOTYyNjlhYTg5MDQxZjQwOTEzNmM3MjQyYTM4ODk1YzlkNWJjY2FkNGYzODlhZjFkN2E0YmQxMzk4YmQwNzJkZmZhODk2MjMzMzk3YSJ9LCJwcmluY2lwYWwiOnsiZW1haWwiOiJUZWNobm9AU2NmQ2xhbi5kZSJ9LCJpYXQiOjEzNjkwNTgzMjc4MzEsImV4cCI6MTM2OTE0NDcyNzgzMSwiaXNzIjoibG9naW4ucGVyc29uYS5vcmcifQ.ROrMgF-eMt-XCJWBLyseTK54j2JKlpQVwamo7r7vWFAMXvOS86lFaathMW_hut263hCXZztHc2raASIuTXSAlrVZbrGE5sd_PGvF60kwlEqXGeE-J9JojNJdzooW4fZkW_uQ3Lj9qPvR9jnvX5FeI1JZABjKFbSZU5BG8hIY4YHtHq-Z-0Ekdx4eXGP_kf6bWHAGkrhIf5ESAP77n91jbRIrw5y31myiDAHZPtx4dSjkN-tRIKHNC57WCdVWYiaYVVNR_bJ34mbM_2AHgEzszrnec1wavcZ-MDOWamsuHaTLBLgrRpFMfzXE_YPi397JbjtjM-EP-hXsH7Vp9ALV3g~eyJhbGciOiJEUzEyOCJ9.eyJleHAiOjEzNjkwNjA0NTU5NTksImF1ZCI6Imh0dHA6Ly9rY2MubGFwdG9wLnBzLXdlYmZvcmdlLm5ldCJ9.f8U4L2j_5MvRp7PZ8lYxhFxF5iUIDTxwVsJocvW8XaPAUCAySVvmNg'), 'json', 200, TRUE);
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