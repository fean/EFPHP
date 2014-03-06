<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'Entities/EntityLayer.php';
    $e = __Entity::getEntities('Entities.xml');
    echo $e[0]->freubels;
    print_r($e);
?>