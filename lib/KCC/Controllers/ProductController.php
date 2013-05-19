<?php

namespace KCC\Controllers;

use KCC\Entities\Product;
use KCC\Entities\CountedProduct;
use Webforge\Common\DateTime\Date;

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

  public function getEntities(Array $query = array(), $subResource = NULL) {
    if ($subResource === 'counted') {
      return $this->getCountedProducts($query);
    } else {
      return parent::getEntities($query, $subResource);
    }
  }

  public function insertEntity(\stdClass $requestData, $subResource = NULL) {
    if ($subResource === 'counted') {
      return $this->saveCountedProducts($requestData);
    } else {
      return parent::insertEntity($requestData, $subResource);
    }
  }

  public function saveCountedProducts($requestData) {
    if (!isset($requestData->user)) {
      throw new \Psc\Exception('user key is missing in requestData. Avaible keys: '.implode(',', array_keys((array) $requestData)));
    }

    $user = $this->hydrate('KCC\Entities\User', $requestData->user);
    $em = $this->dc->getEntityManager();

    $q = $em->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery($user->getEmail());

    foreach ($requestData->countedProductsByDay as $day => $countedProducts) {
      $sort = 1;
      $day = Date::parse('Y-m-d', $day);
      $countedProductsToDelete = $q->setParameter('day', $day->format('Y-m-d'))->getResult();

      foreach ($countedProductsToDelete as $countedProduct) {
        $em->remove($countedProduct);
      }

      foreach ($countedProducts as $countedProduct) {
        $product = $this->hydrate('KCC\Entities\Product', $countedProduct['productId']);

        $countedProduct = new CountedProduct(
          $product, 
          (float) $countedProduct['amount'], 
          $day,
          $user,
          $sort++
        );
        $em->persist($countedProduct);
      }
    }

    $em->flush();
    return (object) array('saved'=>true);
  }

  public function getCountedProducts(Array $query) {
    $user = $this->hydrate('KCC\Entities\User', $query['user']);
    $day = Date::parse('Y-m-d', $query['day']);

    $countedProducts = $this->dc->getRepository('KCC\Entities\CountedProduct')->getUserByDayQuery($user->getEmail(), $day)->getResult();
    return (object) array(
      'user'=>$user->getEmail(),
      'countedProductsByDay'=>$this->groupByDay($countedProducts)
    );
  }

  protected function groupByDay(Array $countedProducts) {
    $data = array();

    foreach ($countedProducts as $countedProduct) {
      $data[$countedProduct->getDay()->format('Y-m-d')][] = $countedProduct->export();
    }

    return (object) $data;
  }
}
?>