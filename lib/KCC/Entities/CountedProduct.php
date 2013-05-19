<?php

namespace KCC\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="KCC\Entities\CountedProductRepository")
 * @ORM\Table(name="counted_products")
 */
class CountedProduct extends CompiledCountedProduct {

  public function export() {
    return (object) array(
      'productId'=>$this->product->getId(),
      'amount'=>$this->getAmount()
    );
  }
  
  public function getEntityName() {
    return 'KCC\Entities\CountedProduct';
  }
}