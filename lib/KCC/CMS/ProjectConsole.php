<?php

namespace KCC\CMS;

use Psc\System\Console\ProjectCompileCommand;

class ProjectConsole extends \Psc\CMS\ProjectConsole {

  public function addCommands() {
    parent::addCommands();

    $this->cli->addCommands(array(
      new ProjectCompileCommand()
      //new DeployCommand()
    ));
  }
}
