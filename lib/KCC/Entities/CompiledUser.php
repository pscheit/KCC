<?php

namespace KCC\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledUser extends \Psc\CMS\User {
  
  /**
   * @ORM\Id
   * @ORM\Column(type="string")
   * @var string
   */
  protected $email;
  
  public function __construct($email) {
    $this->setEmail($email);
  }
  
  public function getEntityName() {
    return 'KCC\Entities\CompiledUser';
  }
}
?>