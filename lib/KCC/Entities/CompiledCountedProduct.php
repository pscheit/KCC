<?php

namespace KCC\Entities;

use Webforge\Common\DateTime\Date;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledCountedProduct extends \Psc\CMS\AbstractEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var float
   * @ORM\Column(type="float")
   */
  protected $amount;
  
  /**
   * @var Webforge\Common\DateTime\Date
   * @ORM\Column(type="PscDate")
   */
  protected $day;
  
  /**
   * @var integer
   * @ORM\Column(type="integer", nullable=true)
   */
  protected $sort;
  
  /**
   * @var KCC\Entities\Product
   * @ORM\ManyToOne(targetEntity="KCC\Entities\Product")
   * @ORM\JoinColumn(nullable=false, onDelete="cascade")
   */
  protected $product;
  
  /**
   * @var KCC\Entities\User
   * @ORM\ManyToOne(targetEntity="KCC\Entities\User")
   * @ORM\JoinColumn(referencedColumnName="email", nullable=false, onDelete="cascade")
   */
  protected $user;
  
  public function __construct(Product $product, $amount, Date $day, User $user, $sort = NULL) {
    $this->setProduct($product);
    $this->setAmount($amount);
    $this->setDay($day);
    $this->setUser($user);
    if (isset($sort)) {
      $this->setSort($sort);
    }
  }
  
  /**
   * @return integer
   */
  public function getId() {
    return $this->id;
  }
  
  /**
   * Gibt den Primärschlüssel des Entities zurück
   * 
   * @return mixed meistens jedoch einen int > 0 der eine fortlaufende id ist
   */
  public function getIdentifier() {
    return $this->id;
  }
  
  /**
   * @param mixed $identifier
   * @chainable
   */
  public function setIdentifier($id) {
    $this->id = $id;
    return $this;
  }
  
  /**
   * @return float
   */
  public function getAmount() {
    return $this->amount;
  }
  
  /**
   * @param float $amount
   */
  public function setAmount($amount) {
    $this->amount = $amount;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\Date
   */
  public function getDay() {
    return $this->day;
  }
  
  /**
   * @param Webforge\Common\DateTime\Date $day
   */
  public function setDay(Date $day) {
    $this->day = $day;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getSort() {
    return $this->sort;
  }
  
  /**
   * @param integer $sort
   */
  public function setSort($sort) {
    $this->sort = $sort;
    return $this;
  }
  
  /**
   * @return KCC\Entities\Product
   */
  public function getProduct() {
    return $this->product;
  }
  
  /**
   * @param KCC\Entities\Product $product
   */
  public function setProduct(Product $product) {
    $this->product = $product;
    return $this;
  }
  
  /**
   * @return KCC\Entities\User
   */
  public function getUser() {
    return $this->user;
  }
  
  /**
   * @param KCC\Entities\User $user
   */
  public function setUser(User $user) {
    $this->user = $user;
    return $this;
  }
  
  public function getEntityName() {
    return 'KCC\Entities\CompiledCountedProduct';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'amount' => new \Psc\Data\Type\FloatType(),
      'day' => new \Psc\Data\Type\DateType(new \Psc\Code\Generate\GClass('Webforge\\Common\\DateTime\\Date')),
      'sort' => new \Psc\Data\Type\PositiveIntegerType(),
      'product' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('KCC\\Entities\\Product')),
      'user' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('KCC\\Entities\\User')),
    ));
  }
}
?>