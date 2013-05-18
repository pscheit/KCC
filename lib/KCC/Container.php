<?php

namespace KCC;

use Psc\Doctrine\DCPackage;
use Psc\CMS\Controller\LanguageAware;
use Hagedorn\Entities\Page;
use Hagedorn\Entities\NavigationNode;
use Webforge\Common\String AS S;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

/**
 * 
 */
class Container extends \Psc\CMS\Roles\AbstractContainer {

  protected $mustacheEngine;

  public function __construct($controllersNamespace, DCPackage $dc, Array $languages, $language,  ContentStreamConverter $contentStreamConverter = NULL) {
    parent::__construct($controllersNamespace ?: 'KCC\Controllers', $dc, $languages, $language, $contentStreamConverter);
  }


  protected function createContentStreamConverter() {
    //return new ContentStreamConverter($this);
  }
}
