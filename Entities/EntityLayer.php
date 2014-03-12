<?php
    //Check the php version, under PHP 5.2.7 PHP_VERSION_ID doesn't exist.
    if(!defined('PHP_VERSION_ID'))
        trigger_error('A newer php version is required to use this product.', E_USER_ERROR);

    //Import the dependencies
    require 'Includes/Entities.inc.php';
    require 'Includes/ResultLayer.inc.php';
    require 'Includes/schemahandler.inc.php';

    class __Context {
        private $PDO, $structural, $srvtype, $server, $database, $user, $password, $entities;

        const DSN_MYSQL = 'mysql:host=%s;dbname=%s';
        const DSN_MSSQL = 'sqlsrv:server=%s;database=%s;';

        public function __construct($lazy, $srvtype, $path, $structural, $server, $database, $user, $password) {
            try {
                if(!$lazy)
                    $this->PDO = new PDO(sprintf($srvtype == __ServerType::MYSQL ? DSN_MYSQL : DSN_MSSQL, $server, $database), $user, $password);
            } catch (Exception $e) {
                if ($structural) {
                    trigger_error($e->message, E_USER_ERROR);
                } else {
                    throw $e;
                }
            }
            $this->structural = $structural;
            $this->server = $server;
            $this->database = $database;
            $this->user = $user;
            $this->password = $password;
            $this->srvtype = $srvtype;
            $this->entities = __Entity::getEntities($path);
        }

        public function __destruct() {
            $PDO = null;
        }

        public function getEntity($entity, $properties) {
            try {
                $t_entity;
                foreach ($this->entities as $_entity) {
                    if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                        $t_entity = $_entity;
                        break;
                    }
                }
				
				if ($t_entity == null)
					throw new Exception('The entity couldnt be found.');
            
                if ($this->PDO == null)
                    $this->PDO = new PDO(sprintf(($this->srvtype == __ServerType::MYSQL ? self::DSN_MYSQL : self::DSN_MSSQL), $this->server, $this->database), $this->user, $this->password);
                $r_entity = __Schema::getEntity($this->PDO, $t_entity, $properties);
                if (!$this->structural) {
                    if (count($r_entity) > 1) {
                        $t_result = array();
                        foreach($r_entity as $entity) {
                            array_push($t_result, (object)$entity);
                        }
                        return $t_result;
                    } else {
                        if (count($r_entity) > 0) {
                            return (object)$r_entity[0];
                        } else {
                            return $r_entity;
                        }
                    }
                } else {
                    return $r_entity;
                }
            } catch (Exception $e) {
                throw $e;
            }
        } 

        public function saveEntity($entity, $properties) {
            try {
                $t_entity;
                foreach ($this->entities as $_entity) {
                    if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                        $t_entity = $_entity;
                        break;
                    }
                }
				
				if ($t_entity == null)
					throw new Exception('The entity couldnt be found.');
            
                if ($this->PDO == null)
                    $this->PDO = new PDO(sprintf(($this->srvtype == __ServerType::MYSQL ? self::DSN_MYSQL : self::DSN_MSSQL), $this->server, $this->database), $this->user, $this->password);
                return __Schema::saveEntity($this->PDO, $t_entity, $properties);
            } catch (Exception $e) {
                throw $e;
            }
        }

        public function getEntities($entity) {
            try {
                $t_entity;
                foreach ($this->entities as $_entity) {
                    if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                        $t_entity = $_entity;
                        break;
                    }
                }
				
				if ($t_entity == null)
					throw new Exception('The entity couldnt be found.');
            
                if ($this->PDO == null)
                    $this->PDO = new PDO(sprintf(($this->srvtype == __ServerType::MYSQL ? self::DSN_MYSQL : self::DSN_MSSQL), $this->server, $this->database), $this->user, $this->password);
                $result = __Schema::getEntities($this->PDO, $t_entity);
                if(!$this->structural) {
                    if (count($result) > 1) {
                        $t_result = array();
                        foreach($result as $entity) {
                            array_push($t_result, (object)$entity);
                        }
                        return $t_result;
                    } else {
                        if (count($result) > 0) {
                            return (object)$result[0];
                        } else {
                            return $result;
                        }
                    }
                } else {
                    return $result;
                }
            } catch(Exception $e) {
                throw $e;
            }
        }

        public function create($entity) {
            try {
                $t_entity;
                foreach ($this->entities as $_entity) {
                    if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                        $t_entity = $_entity;
                        break;
                    }
                }
				
				if ($t_entity == null)
					throw new Exception('The entity couldnt be found.');
            
                if ($this->PDO == null)
                    $this->PDO = new PDO(sprintf(($this->srvtype == __ServerType::MYSQL ? self::DSN_MYSQL : self::DSN_MSSQL), $this->database), $this->user, $this->password);
                return __Schema::create($this->PDO, $t_entity);
            } catch(Exception $e) {
                throw $e;
            }
        }

        public function createEntities() {
            try {         
                if ($this->PDO == null)
                    $this->PDO = new PDO(sprintf(($this->srvtype == __ServerType::MYSQL ? self::DSN_MYSQL : self::DSN_MSSQL), $this->server, $this->database), $this->user, $this->password);
                return __Schema::createAll($this->PDO, $this->entities);
            } catch(Exception $e) {
                throw $e;
            }
        }

        public function inSchema($entity) {
            try {
                $t_entity;
                foreach ($this->entities as $_entity) {
                    if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                        $t_entity = $_entity;
                        break;
                    }
                }
				
				if ($this->PDO == null)
					$this->PDO = new PDO(sprintf(($this->srvtype == __ServerType::MYSQL ? self::DSN_MYSQL : self::DSN_MSSQL), $this->server, $this->database), $this->user, $this->password);
			return __Schema::matchEntity($this->PDO, $this->database, $this->entities);
			} catch (Exception $e) {
				throw $e;	
			}
        }

        public function entityExists($name) {
            foreach ($this->entities as $entity) {
                if (($entity->name . (strtolower($entity->naming) == 'pluralize' ? 's' : '')) == $name) {
                    return true;
                }
                return false;
            }
        }
    }

    //OO front-end object
    class EntityLayer extends __Context {

        public function __construct($lazy, $srvtype, $path, $server, $database, $user, $password) {
            try {
			parent::__construct($lazy, $srvtype, $path, false, $server, $database, $user, $password);
            } catch(Exception $e) {
                throw $e;
            }
        }

        public function __get($name) {
            if (parent::entityExists($name)) {
                return new __ResultLayer($this, $name);
            } else {
                trigger_error('Undefined property: EntityLayer::$' . $name . '.', E_USER_ERROR);
            }
        }

        public function __set($name, $value) {
            if (parent::entityExists($name)) {
                parent::saveEntity($name, $value);
            } else {
                trigger_error('Undefined property: EntityLayer::$' . $name . '.', E_USER_ERROR);
            }
        }
    }


    //Structural Methods
    function create_context($lazy, $srvtype, $path, $server, $database, $user, $password) {
        try {
		return new __Context($lazy, $srvtype, $path, true, $server, $database, $user, $password);
        } catch(Exception $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    function get_entity($context, $entity, $properties = array()) {
        try{
            return $context->getEntity($entity, $properties);
        } catch(Exception $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    function check_create_entity($context, $entity) {
        try{
            return $context->create($entity);
        } catch(Exception $e) {
            trigger_error($e->message, E_USER_ERROR);
        }
    }

    function check_create_entities($context) {
        try{
            return $context->createEntities($entity);
        } catch(Exception $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    function save_entity($context, $entity, $properties = array()) {
        try{
            return $context->saveEntity($entity, $properties);
        } catch(Exception $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
        }    
    }

    function get_all_entities($context, $entity) {
        try{
            return $context->getEntities($entity);
        } catch(Exception $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
?>