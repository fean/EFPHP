<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'Entities/EntityLayer.php';
    
    Entity::getEntities('Entities.xml');
?>