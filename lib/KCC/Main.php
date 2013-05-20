<?php

namespace KCC;

use Psc\Doctrine\ModelCompiler;
use Psc\Doctrine\DCPackage;
use Kcc\CMS\EntityService;
use Psc\CMS\EntityMeta;
use Psc\System\LocaleHelper;

class Main extends \Psc\CMS\ProjectMain {
  
  //protected $rightContentClass = 'KCC\\CMS\\RightContent';
  protected $containerClass = 'KCC\\Container';
  
  protected $welcomeTemplate;
  
  public function isTesting() {
    if ($this->debugLevel >= 15) {
      return TRUE;
    }
    
    if (\Psc\PSC::inProduction())
      return FALSE;
  }

  public function getConnectionName() {
    if ($this->getProject()->getProduction()) {
      //return 'tests';
    }
    
    return parent::getConnectionName();
  }
  
  public function initServices() {
    $this->getEntityService()
      ->setLanguages($this->getLanguages())
      ->setLanguage($this->getLanguage());

    parent::initServices();
  }
  
  public function initEntityMetaFor(EntityMeta $meta) {
    $en = $meta->getEntityName();

    if ($en === 'product') {
      $meta
        ->setNewLabel('Neues Produkt erstellen')
        ->setLabel('Produkt') ->setTCIColumn('id')
      ;
    } else {
      parent::initEntityMetaFor($meta);
    }
  }
  
  public function getWelcomeTemplate() {
    if (!isset($this->welcomeTemplate)) {
      $this->welcomeTemplate = new \Psc\TPL\Template(array('welcome'));
      $this->welcomeTemplate->setVars(array(
        'project'=>$this->getProject(),
        'main'=>$this,
      ));
    }
    
    return $this->welcomeTemplate;
  }
}
