<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'Entities/EntityLayer.php';
    $e = __Entity::getEntities('Entities.xml');
    try {
        echo __Schema::createAll(new PDO('sqlsrv:server=aq01-app-2k12.authiq.org,1433;database=EF;', 'l.breitkopf', 'Lolvent1'), $e);
    } catch (Exception $e) {
        trigger_error($e->getMessage());
    }
?>