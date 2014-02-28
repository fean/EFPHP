<?php
    //Check the php version
    if(!defined('PHP_VERSION_ID'))
        trigger_error('A newer php version is required to use this product.', E_USER_ERROR);

    //Include the dependencies
    require 'Includes/Entities.inc.php';
    require 'Includes/Settings.inc.php';
    require 'Includes/schemahandler.inc.php';

    class Context {
        private $PDO, $structural, $server, $database, $user, $password;

        const DSN = 'mysql:host=%s;dbname=%s';

        public function __construct($lazy, $structural, $server, $database, $user, $password) {
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
        }

        public function __destruct() {
            $PDO = null;
        }

        public function getEntity($entity, $properties) {
            
        }

        public function saveEntity($entity, $properties) {
            
        }

        public function getEntities($entity) {
            
        }
    }

    class EntityLayer extends Context {
        public function __construct($lazy, $server, $database, $user, $password) {
            try {
                parent::__construct($lazy, false, $server, $database, $user, $password);
            } catch(Exception $e) {
                throw $e;
            }
        }
    }

    function create_context($lazy, $server, $database, $user, $password) {
        try {
            return new Context($lazy, $server, $database, $user, $password);
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