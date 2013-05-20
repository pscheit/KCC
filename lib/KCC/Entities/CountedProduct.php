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
    $product = $this->getProduct();
    return (object) array(
      'amount'=>$this->getAmount(),
      'productId'=>$this->product->getId(),
      'label'=>$product->getLabel(),
      'reference'=>$product->getReference(),
      'unit'=>$product->getUnit(),
      'kcal'=>$product->getKcal()
    );
  }
  
  public function getEntityName() {
    return 'KCC\Entities\CountedProduct';
  }
}