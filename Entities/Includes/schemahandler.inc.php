<?php
class __Schema {
    //Query constants
    const QRY_GETENTITY = 'SELECT %s FROM %s WHERE %s;';
    const QRY_CREATEENTITY = 'INSERT INTO %s(%s) VALUES (%s);';
    const QRY_UPDATEENTITY = 'UPDATE %s SET %s WHERE %s;'

    //Check constants
    const CHK_TABLE = '';

    //Create Constants
    const CRT_TABLE = '';
    const CRT_REFERENCE = '';

    public static function create($entity) {
        
    }

    public static function createAll($entities) {
        
    }

    public static function getEntity($db, $entity, $properties) {
        
    }

    public static function getEntities($db, $entity, $properties) {
        
    }
}
