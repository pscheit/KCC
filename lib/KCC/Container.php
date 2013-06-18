<?php

namespace KCC;

use Psc\Doctrine\DCPackage;
use Psc\CMS\Controller\LanguageAware;
use Hagedorn\Entities\Page;
use Hagedorn\Entities\NavigationNode;
use Webforge\Common\String AS S;


/**
 * 
 */
class Container extends \Psc\CMS\Roles\AbstractContainer {

  public $main;

  public function __construct($controllersNamespace, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    parent::__construct($controllersNamespace ?: 'KCC\Controllers', $dc, $languages, $language, $contentStreamConverter);
  }

  protected function createContentStreamConverter() {
    //return new ContentStreamConverter($this);
  }

  public function getLoggedInUser() {
    $persona = $this->getController('Persona');

    $user = $persona->whoami();

    if (isset($user->email)) {
      return $this->getController('User')->getEntity($user->email);
    }

    // use normal auth for tests
    $this->main->auth(); 
    return $this->main->getAuthController()->getUser();
  }
}
