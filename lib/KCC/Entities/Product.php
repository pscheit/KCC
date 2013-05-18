<?php

namespace KCC\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Webforge\Common\DateTime\DateTime;

/**
 * @ORM\Entity(repositoryClass="KCC\Entities\ProductRepository")
 * @ORM\Table(name="products")
 * @ORM\HasLifecycleCallbacks
 */
class Product extends CompiledProduct {

  public function export() {
    $exported = parent::export();
    $exported->tokens = $this->getTokens();

    return $exported;
  }

  public function getTokens() {
    return array($this->label);
  }

  /**
   * @ORM\PrePersist
   * @ORM\PreUpdate
   */
  public function onPrePersist() {
    if (!isset($this->created)) {
      $this->created = new DateTime();
    }

    $this->updated = new DateTime();
  }

  public function getEntityName() {
    return 'KCC\Entities\Product';
  }
}