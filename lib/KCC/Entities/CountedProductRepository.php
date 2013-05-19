<?php

namespace KCC\Entities;

use Webforge\Common\DateTime\Date;

class CountedProductRepository extends \Psc\Doctrine\EntityRepository {

  public function getUserByDayQuery($email = NULL, Date $day = NULL) {
    $dql = "SELECT countedProduct, user FROM KCC\Entities\CountedProduct AS countedProduct ";
    $dql .= "LEFT JOIN countedProduct.user AS user ";
    $dql .= "WHERE user.email = :email AND countedProduct.day = :day";

    $q = $this->_em->createQuery($dql);

    if (isset($email)) {
      $q->setParameter('email', $email);
    }

    if (isset($day)) {
      $q->setParameter('day', $day->format('Y-m-d'));
    }

    return $q;
  }
}