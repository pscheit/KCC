<?php

namespace KCC;

use Psc\Doctrine\ModelCompiler;
use Psc\Doctrine\DCPackage;
use Kcc\CMS\EntityService;
use Psc\CMS\EntityMeta;
use Psc\System\LocaleHelper;
use Psc\Session\Session;
use Psc\CMS\UserManager;

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

    $this->getFrontController()->getRequestHandler()->addService(
      $this->getService()
    );

    parent::initServices();
  }

  public function getService() {
    return new CMS\Service($this->getProject(), $this->getUserManager(), $this->getEnvironmentContainer());
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

  public function setSession(Session $session) {
    $this->session = $session;
  }

  public function getUser() {
    return $this->getContainer()->getLoggedInUser();
  }

  public function getContainer() {
    if (!isset($this->container)) {
      $this->container = parent::getContainer();
      $this->container->main = $this;
    }

    return $this->container;
  }
}
