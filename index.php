<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'Entities/EntityLayer.php';
    
    $c = new EntityLayer(true, 'mysql', 'Entities.xml', 'aq01-app-2k12.authiq.org', 'test', 'fean', 'L@lvent1');
    print_r($c->Users->find(array('Verified' => true)));
?>