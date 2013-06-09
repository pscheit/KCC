<?php 

use KCC\Main;

$main = new Main();
$main->setLanguage('de');
$main->init();

$main->handleAPIRequest();