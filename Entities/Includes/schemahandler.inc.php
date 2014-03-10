<?php
    
    abstract class __ServerType {
        const MYSQL = 'mysql';
        const MSSQL = 'sqlsrv';
    }

    abstract class SHARED_QRY {
        //Query parts
        const PRT_PREPVAL = '%s = :%s AND ';
    }
    
    abstract class MYSQL_QRY {
        //Query constants
        const QRY_GETENTITY = 'SELECT %s FROM `%s`';
        const QRY_FINDENTITY = 'SELECT %s FROM `%s` WHERE %s';
        const QRY_CREATEENTITY = 'INSERT INTO `%s`(%s) VALUES (%s);';
        const QRY_UPDATEENTITY = 'UPDATE `%s` SET %s WHERE %s;';
    
        //Check constant
        const CHK_TABLE = 'SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table';
    
        //Create Constants
        const CRT_TABLE = 'CREATE TABLE `%s` ( %s );';
        const CRT_REFERENCE = 'ALTER TABLE `%s` %s;';
    
        //Key Constants
        const KEY_PRIMARY = 'PRIMARY KEY (`%s`)';
        const KEY_FOREIGN = 'FOREIGN KEY (%s) REFERENCES %s(%s)';
        const KEY_UNIQUE = 'UNIQUE INDEX (`%s`, ASC)';
    }
    
    abstract class MSSQL_QRY {
        //Query constants
        const QRY_GETENTITY = 'SELECT %s FROM [%s]';
        const QRY_FINDENTITY = 'SELECT %s FROM [%s] WHERE %s';
        const QRY_CREATEENTITY = 'INSERT INTO [%s](%s) VALUES (%s);';
        const QRY_UPDATEENTITY = 'UPDATE [%s] SET %s WHERE %s;';
    
        //Check constant
        const CHK_TABLE = 'SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG = :schema AND TABLE_NAME = :table';
    
        //Create Constants
        const CRT_TABLE = 'CREATE TABLE [%s] ( %s );';
        const CRT_REFERENCE = 'ALTER TABLE [%s] %s;';
    
        //Key Constants
        const KEY_PRIMARY = 'PRIMARY KEY ([%s])';
        const KEY_FOREIGN = 'FOREIGN KEY (%s) REFERENCES %s(%s)';
        const KEY_UNIQUE = 'UNIQUE INDEX ([%s], ASC)';
    }

    class __Schema {
    
        public static function create($db, $entity) {
            $t_qry = '';
            $t_qry .= $entity->identity->name . ' ' . self::translateType($entity->identity->type) . ' NOT NULL PRIMARY KEY';
            foreach ($entity->properties as $property) {
                $t_qry .= ',' . $property->name . ' ' . self::translateType($property->type);
            }
            $stmt = $db->prepare(sprintf(($db->getAttribute(PDO::ATTR_DRIVER_NAME) == __ServerType::MSSQL ? MSSQL_QRY::CRT_TABLE : MYSQL_QRY::CRT_TABLE), 
                ($entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : '')), 
                $t_qry));
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception('The entity couldnt be created: ' . $stmt->errorInfo()[2]);
            }
        }
    
        public static function createAll($db, $entities) {
            $r_errors = array();
            foreach ($entities as $entity) {
                $t_qry = '';
                $t_qry .= $entity->identity->name . ' ' . self::translateType($entity->identity->type) . ' NOT NULL PRIMARY KEY';
                foreach ($entity->properties as $property) {
                    $t_qry .= ',' . $property->name . ' ' . self::translateType($property->type);
                }
                $stmt = $db->prepare(sprintf(($db->getAttribute(PDO::ATTR_DRIVER_NAME) == __ServerType::MSSQL ? MSSQL_QRY::CRT_TABLE : MYSQL_QRY::CRT_TABLE),
                    $entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : ''), 
                    $t_qry));

                print_r($stmt);
                if (!$stmt->execute())
                    array_push($r_errors, $entity->name);
            }
            if (count($r_errors) == 0) {
                return true;
            } else {
                $t_errors = '';
                foreach ($r_errors as $error) {
                    $t_errors .= $error . ', ';
                }
                throw new Exception('Error upon creation: ' . trim($t_errors, ', ') . ' couldnt be created.');
            }
        }
    
        public static function getEntity($db, $entity, $properties = array()) {
            $t_qry = '';
            $stmt;
            if (count($properties) == 0) {
                $stmt = $db->prepare(sprintf(($db->getAttribute(PDO::ATTR_DRIVER_NAME) == __ServerType::MSSQL ? MSSQL_QRY::QRY_GETENTITY : MYSQL_QRY::QRY_GETENTITY),
                    '*',
                    $entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : '')));
            } else {
                $p_qry = '';
                foreach ($properties as $p_name => $p_value) {
                    $p_qry .= sprintf(SHARED_QRY::PRT_PREPVAL, $p_name, $p_name);
                }
                $p_qry = substr($p_qry, 0, strlen($p_qry) - 5);
                $stmt = $db->prepare(sprintf(($db->getAttribute(PDO::ATTR_DRIVER_NAME) == __ServerType::MSSQL ? MSSQL_QRY::QRY_FINDENTITY : MYSQL_QRY::QRY_FINDENTITY),
                    '*',
                    $entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : ''),
                    $p_qry));
                foreach ($properties as $p_name => $p_value) {
                    $stmt->bindValue(':' . $p_name, $p_value);
                }
            }
            
            try {
                if ($stmt->execute()) { 
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    throw new Exception('Couldnt get the entity: ' . $stmt->errorInfo()[2]);
                }
            } catch (Exception $e) {
                throw $e;   
            }
        }
    
        public static function getEntities($db, $entity) {
            
        }

        public static function checkEntity($db, $entity) {
            
        }

        private static function translateType($type) {
            switch (strtolower($type)) {
                case 'int': 
                    return 'int';
                case 'integer':
                    return 'int';
                case 'string':
                    return 'nvarchar(4000)';
                case 'date':
                    return 'date';
                case 'datetime':
                    return 'datetime';
                case 'boolean':
                    return 'bit';
                case 'data':
                    return 'blob';
                default:
                    return 'nvarchar(4000)';
            }
        }
    }
