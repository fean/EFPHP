<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'Entities/EntityLayer.php';
    $e = __Entity::getEntities('Entities.xml');
    try {
        $db = new PDO('sqlsrv:server=aq01-app-2k12.authiq.org;database=EF;', 'l.breitkopf', 'Lolvent1');
        __Schema::create($db, $e[0]);
        echo __Schema::getEntity($db, $e[0], array('ID' => 1, 'Username' => 'fean'));
    } catch (Exception $e) {
        trigger_error($e->getMessage());
    }
?>