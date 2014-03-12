<?php
    
    abstract class __ServerType {
        const MYSQL = 'mysql';
        const MSSQL = 'sqlsrv';
    }

    abstract class SHARED_QRY {
        //Query parts
        const PRT_PREPVAL = '%s = :%s AND ';

        //Check constant
        const CHK_TABLE = 'SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table';
        const CHK_COLUMN = 'SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table';
    }
    
    abstract class MYSQL_QRY {
        //Query constants
        const QRY_GETENTITY = 'SELECT %s FROM `%s`';
        const QRY_FINDENTITY = 'SELECT %s FROM `%s` WHERE %s';
        const QRY_CREATEENTITY = 'INSERT INTO `%s`(%s) VALUES (%s);';
        const QRY_UPDATEENTITY = 'UPDATE `%s` SET %s WHERE %s;';
    
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
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    throw new Exception('Couldnt get the entity: ' . $stmt->errorInfo()[2]);
                }
            } catch (Exception $e) {
                throw $e;   
            }
        }
    
        public static function getEntities($db, $entity) {
            $r_entities = array();
            $stmt = $db->prepare(sprintf(($db->getAttribute(PDO::ATTR_DRIVER_NAME) == __ServerType::MSSQL ? MSSQL_QRY::QRY_GETENTITY : MYSQL_QRY::QRY_GETENTITY),
                '*', 
                $entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : '')));

            try {
                if ($stmt->execute()) {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    throw new Exception('Couldnt get the entities: ' . $stmt->errorInfo()[2]);
                }
            } catch (Exception $e) {
                throw $e;
            }
            return $r_entities;
        }

        public static function saveEntity($db, $entity, $values) {
            $t_names = '';
			$t_params = '';
			foreach($values as $_name => $_value) {
				$t_names .= $_name . ',';
				$t_params .= ':' . $_name . ',';
			}
			$t_names = trim($t_names, ',');
			$t_params = trim($t_params, ',');
			
			$stmt = $db->prepare(sprintf(($db->getAttribute(PDO::ATTR_DRIVER_NAME) == __ServerType::MSSQL ? MSSQL_QRY::QRY_CREATEENTITY : MYSQL_QRY::QRY_CREATEENTITY),
				$entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : ''),
				$t_names,
				$t_params));
			foreach($values as $_name => $_value) {
				$stmt->bindValue(':' . $_name, $_value);
			}
			
			try {
				if($stmt->execute()) {
					return true;
				} else {
				throw new Exception('Couldnt save entity: ' . $stmt->errorInfo()[2]);
				}
			} catch(Exception $e) {
				throw $e;
			}
        }

        public static function matchEntity($db, $schema, $entity) {
            $stmt = $db->prepare(SHARED_QRY::CHK_TABLE);
            print_r($stmt);
            if($stmt->execute(array(':schema' => $schema, ':table' => $entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : '')))) {
                print_r($stmt->fetch(PDO::FETCH_ASSOC));
                if (count($stmt->fetch(PDO::FETCH_ASSOC) > 0)) {
                    $stmt = $db->prepare(SHARED_QRY::CHK_COLUMN);
                    print_r($stmt);
                    if($stmt->execute(array(':table' => $entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : '')))) {
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        print_r($result);
                        if(count($result) == count($entity->properties) + 1) {
                            foreach($result as $column) {
                                $t_val = false;
                                foreach ($entity->properties as $property) {
                                    if ($property->name == $column['COLUMN_NAME'] && self::tanslateType($property->type, true) == $column['DATA_TYPE']) {
                                        $t_val = true;
                                        break;
                                    }
                                }
                                if(!$t_val) {
                                    return false;
                                }
                            }
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        throw new Exception('Couldnt perform checking: ' . $stmt->errorInfo()[2]);
                    }
                } else {
                    return false;
                }
            } else {
                throw new Exception('Couldnt perform checking: ' . $stmt->errorInfo()[2]);
            }
        }

        private static function translateType($type, $plain = false, $length = 0) {
            switch (strtolower($type)) {
                case 'int': 
                    return 'int';
                case 'integer':
                    return 'int';
                case 'string':
                    return 'nvarchar' . ($plain ? '' : ('(' . ($length > 0 ? $length : 4000) . ')'));
                case 'date':
                    return 'date';
                case 'datetime':
                    return 'datetime';
                case 'boolean':
                    return 'bit';
                case 'data':
                    return 'blob';
                default:
                    return 'nvarchar' . ($plain ? '' : ('(' . ($length > 0 ? $length : 4000) . ')'));
            }
        }
    }
