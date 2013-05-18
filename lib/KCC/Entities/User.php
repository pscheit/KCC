<?php

namespace KCC\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="KCC\Entities\UserRepository")
 * @ORM\Table(name="users")
 */
class User extends CompiledUser {
  
  public function getEntityName() {
    return 'KCC\Entities\User';
  }
}