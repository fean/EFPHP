<?php
    class Entity {
        public function __get($name) {
           return $this->data[$name];
        }
    
        public function __set($name, $value) {
           $this->data[$name] = $value;
        }
    
        public static function getEntities($path) {
            $doc = simplexml_load_file($path);
            
        }
    }
