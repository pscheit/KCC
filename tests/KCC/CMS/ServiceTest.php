<?php

namespace KCC\CMS;

use KCC\Entities\User;
use Psc\Net\ServiceRequest;
use Psc\Code\Test\Mock\SessionMock;
use KCC\Main;


class ServiceTest extends \Psc\Doctrine\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'KCC\\CMS\\Service';
    parent::setUp();

    $this->main = new Main($this->getProject(), $this->dc);
    $this->main->init();
    $this->service = $this->main->getService();
  }

  public function testUserCanLogin() {
    $user = $this->insertUser();

    // inject session + personaController
    $this->main->getEnvironmentContainer()->setSession($session = new SessionMock());
    $session->init();

    $this->service->setPersonaController(
      $persona = $this->getMockBuilder('Webforge\Persona\Controller')
        ->setConstructorArgs(array(NULL, $this->main->getEnvironmentContainer()))
        ->setMethods(array('verify'))
      ->getMock()
    );

    $assertion = 'sldfjlsdjflsdjf009x';

    $persona
      ->expects($this->once())->method('verify')
      ->with($this->equalTo($assertion))
      ->will($this->returnValue((object) array('email'=>'p.scheit@ps-webforge.com')))
    ;

    $response = $this->service->route(ServiceRequest::create('POST',array('kcc', 'auth', 'login'), array('assertion'=>$assertion)));

    $this->assertInstanceOf('Psc\CMS\User', $responseUser = $response->getBody());
    $this->assertSame($responseUser, $user);

    $this->assertSame($user, $this->main->getContainer()->getLoggedInUser());
  }

  public function insertUser() {
    $user = new User('p.scheit@ps-webforge.com');
    $user->hashPassword('hae');
    $this->em->persist($user);

    return $user;
  }
}
