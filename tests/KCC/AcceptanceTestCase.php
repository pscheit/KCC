<?php

namespace KCC;

use Psc\Net\ServiceRequest;
use KCC\Entities\User;

class AcceptanceTestCase extends \Psc\Doctrine\DatabaseTestCase {

  protected $main, $requestHandler;
  protected $guzzleResponse;

  public function setUp() {
    parent::setUp();

    $this->main = new Main($this->getProject(), $this->dc);
    $this->main->setSession(new \Psc\Code\Test\Mock\SessionMock());
    $this->main->init();
    $this->requestHandler = $this->main->getFrontController()->getRequestHandler();
    $this->requestHandler->setDebugLevel(15);
  }

  protected function setUpDatabase() {

    $user = new User(\Psc\PSC::getProjectsFactory()->getHostConfig()->req('cmf.user'));
    $user->hashPassword(\Psc\PSC::getProjectsFactory()->getHostConfig()->req('cmf.password'));
    $this->em->persist($user);

    return parent::setUpDatabase();
  }

  public function initAcceptanceTester($tester) {
    $this->tester = $tester;
  }

  /**
   * @return Psc\Net\ServiceResponse
   */
  protected function dispatchRequest($method, $url, $body = NULL, $format = 'json', \Closure $withRequest = NULL) {
    $headers = array();

    $serviceRequest = ServiceRequest::create($method, $parts = explode('/', trim($url, '/')), $body);
    $serviceRequest->setQuery(array());

    if (isset($withRequest)) {
      $withRequest($serviceRequest);
    }
    
    return $this->requestHandler->route($serviceRequest);
  }

  protected function onNotSuccessFulTest(\Exception $e) {
    if (isset($this->requestHandler)) {
      print $this->requestHandler->getDebugInfo();
    }

    if (isset($this->guzzleResponse)) {
      print '------------ Acceptance - Guzzle (Fail) ------------'."\n";
      print '--------- Request ---------'."\n";
      print $this->guzzleRequest."\n";
      print '--------- Response ---------'."\n";
      print $this->guzzleResponse;
      print "\n";
      print '------------ / Guzzle ------------'."\n";
    }

    if (isset($this->tester)) {
      if (isset($this->html)) {
        print $this->html;
      }

      print '------------ Acceptance (Fail) ------------'."\n";
      print "\n";
      print $this->tester->getLog();
      print '------------ /Acceptance ------------'."\n";
    }

    throw $e;
  }
}