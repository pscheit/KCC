<?php

namespace KCC;

use KCC\Entities\Product;
use KCC\Entities\User;
use KCC\Entities\CountedProduct;
use Webforge\Common\DateTime\Date;

class APITest extends AcceptanceTestCase {

  protected $client;

  public function setUp() {
    parent::setUp();
    $this->client = new \Guzzle\Http\Client($this->getProject()->getBaseUrl());

    $hostConfig = $this->getProject()->getHostConfig();
    $this->client->getEventDispatcher()->addListener('client.create_request', function (\Guzzle\Common\Event $e) use ($hostConfig) {
      $request = $e['request'];

      $request->setAuth($hostConfig->req('cmf.user'),$hostConfig->req('cmf.password'));
      $request->setHeader('X-Psc-Cms-Connection', 'tests');
      $request->setHeader('X-Psc-Cms-Debug-Level', 15);
      if ($request->getMethod() != 'POST' && $request->getMethod() != 'GET') {
        $request->setHeader('X-Psc-Cms-Request-Method', $request->getMethod());
      }

      $sendDebugSessionCookie = FALSE;
      if ($sendDebugSessionCookie && $hostConfig->get('uagent-key') != NULL) {
        $request->addCookie('XDEBUG_SESSION', $hostConfig->get('uagent-key'));
      }

      if (!$request->hasHeader('Accept')) {
        $request->setHeader('Accept', $request->getHeader('Content-Type').'; q=0.8');
      }
      
    });
  }

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
    $user = $this->insertUser('ik');
    $this->insertSomeProducts();

    $body = '{
      "countedProductsByDay": {"2013-05-19":[{"amount":"130","productId":"1"},{"amount":"120","productId":"3"}]}
     }';

    $request = $this->client->post('/entities/products/counted', array('Content-Type'=>'application/json'), $body);
    $request->setAuth($user->getEmail(), 'pw');
    $this->dispatchGuzzle($request, 200, 'json');
    $this->em->clear();

    $products = $this->em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery($user->getEmail(), new Date('2013-05-19'))->getResult();

    $this->assertCount(2, $products);
    $this->assertContainsOnlyInstancesOf('KCC\Entities\CountedProduct', $products);

    foreach ($products as $countedProduct) {
      $this->assertEquals('2013-05-19', $countedProduct->getDay()->format('Y-m-d'));
      $this->assertGreaterThan(0, $countedProduct->getAmount());
    }
  }

  public function testSavesCountedProductsByDateNotTwice() {
    $this->resetDatabaseOnNextTest();
    $user = $this->insertUser('p.scheit');
    $this->insertSomeProducts();

    $body = '{
      "countedProductsByDay": {"2013-05-19":[{"amount":"130","productId":"1"},{"amount":"120","productId":"3"}]}
     }';

    $request = $this->client->post('/entities/products/counted', array('Content-Type'=>'application/json'), $body);
    $request->setAuth($user->getEmail(), 'pw');
    $response = $this->dispatchGuzzle($request, 200, 'json');
    $this->em->clear();

    $products = $this->em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery('p.scheit@ps-webforge.com', new Date('2013-05-19'))->getResult();
    $this->assertCount(2, $products);

    $request = $this->client->post('/entities/products/counted', array('Content-Type'=>'application/json'), $body);
    $request->setAuth($user->getEmail(), 'pw');

    $response = $this->dispatchGuzzle($request, 200, 'json');
    $this->em->clear();
    $products = $this->em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery('p.scheit@ps-webforge.com', new Date('2013-05-19'))->getResult();
    $this->assertCount(2, $products);
  }

  public function testGetsAllCountedProductsForADayGroupedByDay() {
    $this->resetDatabaseOnNextTest();
    $user = $this->insertUser('p.scheit');
    $this->insertSomeProducts();

    $body = '{
      "countedProductsByDay": {"2013-05-19":[{"amount":"130","productId":"1"},{"amount":"120","productId":"3"}]}
     }';

    $request = $this->client->post('/entities/products/counted', array('Content-Type'=>'application/json'), $body);
    $request->setAuth($user->getEmail(), 'pw');
    $response = $this->dispatchGuzzle($request, 200, 'json');

    $structure = $this->test->acceptance('product')
      ->dispatch(
        'GET', 
        '/entities/products/counted', 
        array('user'=>'p.scheit@ps-webforge.com', 'day'=>'2013-05-19'), 
        'json', 
        200, 
        TRUE
      );
    
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
    $user->hashPassword('pw');
    $this->em->persist($user);

    return $user;
  }

  protected function dispatchGuzzle($request, $statusCode, $format) {
    $this->guzzleRequest = $request;

    try {
      $this->guzzleResponse = $response = $request->send();
    } catch (\Guzzle\Http\Exception\ServerErrorResponseException $e) {
      //\Psc\Doctrine\Helper::dump($e);
      $this->guzzleResponse = $e->getResponse();
      throw $e;
    }

    $this->assertEquals($statusCode, $response->getStatusCode(), 'StatusCode does not match');

    $contentType = \Psc\Net\HTTP\ResponseConverter::getContentTypeFromFormat($format);
    $this->assertTrue(
      $response->isContentType($contentType),
      sprintf("ContentType does not match\n--- Expected\n+++ Actual\n@@ @@\n-'%s'\n+'%s'", $contentType, $response->getContentType())
    );

    return $response;
  }
}
