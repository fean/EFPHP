<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

   require 'Entities/EntityLayer.php';
    
$c = new EntityLayer(true, 'sqlsrv', 'Entities.xml', 'aq01-app-2k12.authiq.org', 'EF', 'l.breitkopf', 'Lolvent1');
print_r($c->Users = array('ID' => 4, 'Username' => 'testah2', 'Password' => 'lolzor4', 'Mail' => 'testah2@test.com', 'Verified' => false));
?>