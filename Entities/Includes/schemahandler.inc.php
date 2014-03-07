<?php
class __Schema {
    //Query constants
    const QRY_GETENTITY = 'SELECT %s FROM %s WHERE %s;';
    const QRY_CREATEENTITY = 'INSERT INTO %s(%s) VALUES (%s);';
    const QRY_UPDATEENTITY = 'UPDATE %s SET %s WHERE %s;';

    //Check constants
    const CHK_TABLE = 'SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table';

    //Create Constants
    const CRT_TABLE = 'CREATE TABLE `%s` ( %s );';
    const CRT_REFERENCE = 'ALTER TABLE `%s` %s;';

    //Key Constants
    const KEY_PRIMARY = 'PRIMARY KEY (`%s`)';
    const KEY_FOREIGN = 'FOREIGN KEY (%s) REFERENCES %s(%s)';
    const KEY_UNIQUE = 'UNIQUE INDEX (`%s`, ASC)';

    public static function create($db, $entity) {
        
    }

    public static function createAll($db, $entities) {
        
    }

    public static function getEntity($db, $entity, $properties) {
        
    }

    public static function getEntities($db, $entity, $properties) {
        
    }
}
