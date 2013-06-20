<?php

namespace Hagedorn\CMS;

use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Webforge\Framework\Container as WebforgeContainer;
use Psc\System\Deploy\Deployer;
use Psc\CMS\Project;

class DeployCommand extends \Psc\System\Console\DeployCommand {
  
  protected function initProperties($mode) {
    $this->hostName = 'pegasus';
    $this->baseUrl = 'kcc.ps-webforge.com';
    $this->vhostName = 'kcc.ps-webforge.com';
    $this->staging = FALSE;
    $this->server = 'www-data@pegasus.ps-webforge.net';
  }
  
  protected function initTasks(Deployer $deployer, Project $project, $mode, WebforgeContainer $container) {
    $deployer->addTask($deployer->createTask('CreateAndWipeTarget'));

    $deployer->addTask(
      $deployer->createTask('CopyProjectSources')
        ->addAdditionalPath('application/')
    );
    
    $deployer->addTask(
      $deployer->createTask('CreateBootstrap')
        ->setComposerAutoLoading(TRUE)
        ->addModule('Symfony')
        ->addModule('Doctrine')
    );
    
    $deployer->addTask($deployer->createTask('DeployPscCMS'));
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
          ->setServerName('kcc.ps-webforge.com')
          //->setAuth('/', '%vhost%etc/auth/public', 'hagedorn access')
          //->setAuth('/admin/', '%vhost%etc/auth/admin', 'hagedorn admin access')
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
