#!/usr/bin/env php
<?php

use Psc\System\Dir;

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php';

$console = new \KCC\CMS\ProjectConsole($project = $GLOBALS['env']['container']->getProject(), $project->getModule('Doctrine'));
$console->run();