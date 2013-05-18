<?php

namespace Hagedorn\CMS;

use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Webforge\Framework\Container as WebforgeContainer;
use Psc\System\Deploy\Deployer;
use Psc\CMS\Project;

class DeployCommand extends \Psc\System\Console\DeployCommand {
  
  protected function initProperties($mode) {
    if ($mode === 'staging') {
      $this->hostName = 'hagedorn';
      $this->baseUrl = 'hagedorn.ps-webforge.net';
      $this->vhostName = 'hagedorn.ps-webforge.net';
      $this->staging = TRUE;
      $this->variant = 'staging';
    } else {
      $this->hostName = 'hagedorn';
      $this->baseUrl = 'preview.hagedorn-gmbh.de';
      $this->vhostName = 'www.hagedorn-gmbh.de';
      $this->staging = FALSE;
    }
    $this->server = 'www-data@hagedorn.ps-webforge.net';
  }
  
  protected function initTasks(Deployer $deployer, Project $project, $mode, WebforgeContainer $container) {
    $deployer->addTask($deployer->createTask('CreateAndWipeTarget'));

    $deployer->addTask(
      $deployer->createTask('CopyProjectSources')
        ->addAdditionalPath('www/cms/admin/')
        ->addAdditionalPath('application/')
        ->addAdditionalPath('www/cms/sce/')
    );
    
    $deployer->addTask(
      $deployer->createTask('CreateBootstrap')
        ->setComposerAutoLoading(TRUE)
        ->addModule('Symfony')
        ->addModule('Doctrine')
        ->addModule('Imagine')
    );
    
    $deployer->addTask($deployer->createTask('DeployPscCMS')); // installiert phars und so 
    $deployer->addTask($deployer->createTask('DeployDoctrine'));
    
    $configureApache =
       $mode === 'staging'
       ?
       $deployer->createTask('ConfigureApache')
          ->setServerName('hagedorn.ps-webforge.net')
          ->setServerNameCms('cms.hagedorn.ps-webforge.net')
          ->setAuth('/', '%vhost%etc/auth/public', 'hagedorn staging access')
       :
       $deployer->createTask('ConfigureApache')
          ->setServerName('www.hagedorn-gmbh.de')
          ->setServerAlias('hagedorn-gmbh.de preview.hagedorn-gmbh.de server.hagedorn-gmbh.de *.messerschleifen.de *.hagedorn-messer.de *.hagedorn-messer.com')
          ->setServerNameCms('cms.hagedorn-gmbh.de')
          //->setAuth('/', '%vhost%etc/auth/public', 'hagedorn access')
          ->setAuth('/admin/', '%vhost%etc/auth/admin', 'hagedorn admin access')
      ;

    $deployer->addTask(
      $configureApache
        ->setTemplate('cms-public')
        ->setHtaccess($project->getBase()->getFile('etc/build/.deploy.htaccess')->getContents())
        ->setCmsHtaccess($project->getBase()->getFile('etc/build/.deploy.cms-htaccess')->getContents())
        ->addAlias('/sitemap', '%vhost%files/cache/sitemap')
    );

    if ($mode === 'staging') {
      $deployer->addTask(
        $deployer->createTask('UnisonSync')
          ->setProfile('automatic.hagedorn.ps-webforge.net@hagedorn.ps-webforge.net')
      );
    }
  }

  //protected function updateComposer($project) {}
}
?>