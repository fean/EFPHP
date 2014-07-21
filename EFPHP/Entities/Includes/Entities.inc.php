<?php
    class __Entity {
        public $name, $naming, $identity;
        public $properties = array();
        public $references = array();

        public function __construct($name, $naming, $identity, $properties, $references = null) {
            if($properties != null && count($properties) > 0) {
                $this->properties = $properties;
            }
            if($references != null && count($references) > 0) {
                $this->references = $references;
            }
            $this->name = $name;
            $this->naming = $naming;
            $this->identity = $identity;
        }
    
        public static function getEntities($path) {
            $doc = simplexml_load_file($path);
            $entities = array();
            foreach ($doc->entity as $entity) {
                $t_identity = new __Property($entity->identity->name, $entity->identity->type);
                $t_properties = array();
                foreach ($entity->property as $property) {
                    $t_property = new __Property($property->name, $property->type);
                    if(property_exists($property, 'security')) {
                        if (property_exists($property->security, 'hash')) {
                            $t_property->security = new __Security($property->security->hash);
                            $t_property->securitySet = true;
                        } elseif (property_exists($property->security, 'encoding')) {
                            $t_property->security = __Security(null, $property->security->encoding);
                            $t_property->securitySet = true;
                        } elseif (property_exists($property->security, 'encryption')) {
                            $t_property->security = __Security(null, null, $property->security->encryption);
                            $t_property->securitySet = true;
                        }
                    }
                    array_push($t_properties, $t_property);
                }
                $t_references = array();
                foreach ($doc->reference as $reference) {
                    if (strtolower($reference->from->entity) == strtolower($entity->set->name) || strtolower($reference->to->entity) == strtolower($entity->set->name)) {
                        array_push($t_references, new __Reference($reference->from->entity, $reference->from->property, $reference->to->entity, $reference->to->property));
                    }
                }
                array_push($entities, new __Entity($entity->set->name, $entity->set->naming, $t_identity, $t_properties, $t_references));
            }
            return $entities;
        }
    }

    class __Reference {
        public $fromEntity, $fromProperty, $toEntity, $toProperty;

        public function __construct($fromEntity, $fromProperty, $toEntity, $toProperty) {
            $this->fromEntity = $fromEntity;
            $this->fromProperty = $fromProperty;
            $this->toEntity = $toEntity;
            $this->toProperty = $toProperty;
        }
    }

    class __Property {
        public $name, $type, $security;
        public $securitySet = false;

        public function __construct($name, $type, $security = null) {
            $this->name = $name;
            $this->type = $type;
            if ($security != null) {
                $this->security = $security;
                $this->securitySet = true;
            } 
        }
    }

    class __Security {
        public $hashing, $encoding, $encryption;
        public $hashingSet = false;
        public $encodingSet = false;
        public $encryptionSet = false;

        public function __construct($hashing = null, $encoding = null, $encryption = null) {
            if($hashing != null) {
                $this->hashing = $hashing;
                $this->hashingSet = true;
            } elseif ($encoding != null) {
                $this->encoding = $encoding;
                $this->encodingSet = true;
            } elseif ($encryption != null) {
                $this->encryption = $encryption;
                $this->encryptionSet = true;
            }
        }
    }