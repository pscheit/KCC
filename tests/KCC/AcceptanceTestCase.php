<?php

namespace KCC;

use Psc\Net\ServiceRequest;

class AcceptanceTestCase extends \Psc\Doctrine\DatabaseTestCase {

  protected $main, $requestHandler;

  public function setUp() {
    parent::setUp();

    $this->main = new Main($this->getProject(), $this->dc);
    $this->main->init();
    $this->requestHandler = $this->main->getFrontController()->getRequestHandler();
    $this->requestHandler->setDebugLevel(15);
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

    if (isset($this->tester)) {
      print '------------ Acceptance (Fail) ------------'."\n";
      print "\n";
      print $this->tester->getLog();
      print '------------ /Acceptance ------------'."\n";
    }

    throw $e;
  }
}