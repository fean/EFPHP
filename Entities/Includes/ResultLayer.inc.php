<?php
    class __ResultLayer implements IteratorAggregate {
        public $data = array();
        private $entity, $context;
    
        public function __construct($context, $entity) {
            $this->context = $context;
            $this->entity = $entity;
        }
    
        public function getIterator() {
            if(count($this->data) < 1)
                $this->data = $this->context->getEntities($this->entity);
            return new ArrayIterator($this->data);
        }
    
        public function get($index) {
            if(count($this->data) < 1)
                $this->data = $this->context->getEntities($this->entity);
            return $this->data[$index];
        }
    
        public function toArray() {
            if(count($this->data) < 1)
                $this->data = $this->context->getEntities($this->entity);
            return $this->data;
        }
    
        public function find($properties) {
            return $this->context->getEntity($this->entity, $properties);
        }
    } 
?>