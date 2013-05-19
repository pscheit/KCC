<?php

namespace KCC\Entities;

use Webforge\Common\DateTime\DateTime;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledProduct extends \Psc\CMS\AbstractEntity {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $label;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $manufacturer;
  
  /**
   * @var float
   * @ORM\Column(type="float")
   */
  protected $reference;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $unit;
  
  /**
   * @var integer
   * @ORM\Column(type="integer")
   */
  protected $kcal;
  
  /**
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="PscDateTime")
   */
  protected $created;
  
  /**
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="PscDateTime", nullable=true)
   */
  protected $updated;
  
  public function __construct($label, $manufacturer, $reference, $unit, $kcal) {
    $this->setLabel($label);
    $this->setManufacturer($manufacturer);
    $this->setReference($reference);
    $this->setUnit($unit);
    $this->setKcal($kcal);
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
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  /**
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getManufacturer() {
    return $this->manufacturer;
  }
  
  /**
   * @param string $manufacturer
   */
  public function setManufacturer($manufacturer) {
    $this->manufacturer = $manufacturer;
    return $this;
  }
  
  /**
   * @return float
   */
  public function getReference() {
    return $this->reference;
  }
  
  /**
   * @param float $reference
   */
  public function setReference($reference) {
    $this->reference = $reference;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getUnit() {
    return $this->unit;
  }
  
  /**
   * @param string $unit
   */
  public function setUnit($unit) {
    $this->unit = $unit;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getKcal() {
    return $this->kcal;
  }
  
  /**
   * @param integer $kcal
   */
  public function setKcal($kcal) {
    $this->kcal = $kcal;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getCreated() {
    return $this->created;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $created
   */
  public function setCreated(DateTime $created) {
    $this->created = $created;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getUpdated() {
    return $this->updated;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $updated
   */
  public function setUpdated(DateTime $updated = NULL) {
    $this->updated = $updated;
    return $this;
  }
  
  public function getEntityName() {
    return 'KCC\Entities\CompiledProduct';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'label' => new \Psc\Data\Type\StringType(),
      'manufacturer' => new \Psc\Data\Type\StringType(),
      'reference' => new \Psc\Data\Type\FloatType(),
      'unit' => new \Psc\Data\Type\StringType(),
      'kcal' => new \Psc\Data\Type\PositiveIntegerType(),
      'created' => new \Psc\Data\Type\DateTimeType(),
      'updated' => new \Psc\Data\Type\DateTimeType(),
    ));
  }
}
?>