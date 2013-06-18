<?php

namespace KCC\CMS;

use Psc\Net\ServiceRequest;
use Psc\CMS\Roles\ControllerContainer;
use Webforge\Persona\Controller as PersonaController;
use Webforge\Persona\Client as Persona;
use Psc\CMS\UserManager;
use Webforge\CMS\EnvironmentContainer;

class Service extends \Psc\CMS\Service\ControllerService {

  protected $um;

  protected $env;

  public function __construct(\Psc\CMS\Project $project, UserManager $um, EnvironmentContainer $container, PersonaController $personaController = NULL) {
    parent::__construct($project);
    $this->um = $um;
    $this->env = $container;
    $this->personaController = $personaController ?: new PersonaController(NULL, $this->env);;
  }
  
  public function routeController(ServiceRequest $request) {
    $r = $this->initRequestMatcher($request);

    $r->matchValue('kcc');

    if ($r->matchRx('/^(auth)$/')) {
      return $this->auth($r);
    }
  }

  public function auth($r) {
    if ($r->matchesValue('login')) {
      $userManager = $this->um;

      return array(
        $this->personaController, 
        'login', 
        array(
          $r->bVar('assertion'), 
          function ($email) use ($userManager) {
            return $userManager->get($email);
          }
        )
      );
    } elseif ($r->matchesValue('whoami')) {
      return array(
        $this->personaController,
        'whoami',
        array()
      );
    } elseif ($r->matchesValue('logout')) {
      return array(
        $this->personaController,
        'logout',
        array()
      );
    }
  }

  public function setPersonaController(PersonaController $ctrl) {
    $this->personaController = $ctrl;
    return $this;
  }
}
