<?php
    //Check the php version, under PHP 5.2.7 PHP_VERSION_ID doesn't exist.
    if(!defined('PHP_VERSION_ID'))
        trigger_error('A newer php version is required to use this product.', E_USER_ERROR);

    //Include the dependencies
    require 'Includes/Entities.inc.php';
    require 'Includes/Settings.inc.php';
    require 'Includes/schemahandler.inc.php';

    class Context {
        private $PDO, $structural, $server, $database, $user, $password, $entities;

        const DSN = 'mysql:host=%s;dbname=%s';

        public function __construct($lazy, $path, $structural, $server, $database, $user, $password) {
            try {
                if(!$lazy)
                    $this->PDO = new PDO(sprintf(self::DSN, $server, $database), $user, $password);
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
            $this->entities = __Entity::getEntities($path);
        }

        public function __destruct() {
            $PDO = null;
        }

        public function getEntity($entity, $properties) {
            $t_entity;
            foreach ($this->entities as $_entity) {
                if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                    $t_entity = $_entity;
                    break;
                }
            }
            
            if ($this->PDO == null)
                $this->PDO = new PDO(sprintf(self::DSN, $this->server, $this->database), $this->user, $this->password);
            $r_entity = __Schema::getEntity($this->PDO, $t_entity, $properties);
            if (!$this->structural) {
                return (object)$r_entity;
            } else {
                return $r_entity;
            }
        } 

        public function saveEntity($entity, $properties) {
            $t_entity;
            foreach ($this->entities as $_entity) {
                if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                    $t_entity = $_entity;
                    break;
                }
            }
            
            if ($this->PDO == null)
                $this->PDO = new PDO(sprintf(self::DSN, $this->server, $this->database), $this->user, $this->password);
            return __Schema::saveEntity($this->PDO, $t_entity, $properties);
        }

        public function getEntities($entity) {
            $t_entity;
            foreach ($this->entities as $_entity) {
                if ($_entity->name == $entity || $_entity->name . (strtolower($_entity->naming) == 'pluralize' ? 's' : '') == $entity) {
                    $t_entity = $_entity;
                    break;
                }
            }
            
            if ($this->PDO == null)
                $this->PDO = new PDO(sprintf(self::DSN, $this->server, $this->database), $this->user, $this->password);
            return __Schema::getEntities($this->PDO, $this->structural, $t_entity, $properties);
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

    class EntityLayer extends Context {

        public function __construct($lazy, $path, $server, $database, $user, $password) {
            try {
                parent::__construct($lazy, $path, false, $server, $database, $user, $password);
            } catch(Exception $e) {
                throw $e;
            }
        }

        public function __get($name) {
            if (parent::entityExists($name)) {
                return parent::getEntities($name);
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

    function create_context($lazy, $path, $server, $database, $user, $password) {
        try {
            return new Context($lazy, $path, $server, $database, $user, $password);
        } catch(Exception $e) {
            trigger_error($e->message, E_USER_ERROR);
        }
    }
    
    function get_entity($context, $entity, $properties) {
        try{
            return $context->getEntity($entity, $properties);
        } catch(Exception $e) {
            trigger_error($e->message, E_USER_ERROR);
        }
    }

    function save_entity($context, $entity, $properties) {
        try{
            return $context->saveEntity($entity, $properties);
        } catch(Exception $e) {
            trigger_error($e->message, E_USER_ERROR);
        }    
    }

    function get_all_entities($context, $entity) {
        try{
            return $context->getEntities($entity);
        } catch(Exception $e) {
            trigger_error($e->message, E_USER_ERROR);
        }
    }
?>