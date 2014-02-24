<?php
    /**
    *An entity representing a user
    *@author Leonard Breitkopf
    */
    class User {
        public $id;
        public $Username;
        public $Mail;
        public $Password;
        public $Verified;
        
        /**
        *The constructor, used to automatically set-up the entity object.
        *
        *@param int $id The user's id.
        *@param string $Username The user's username.
        *@param string $Mail The user's e-mail address.
        *@param bool $Verified A value representing the user's verification state.
        *@param string [$password = ''] This optional parameter represents the user's password.
        *
        *@access public
        */
        public function __construct($id, $Username, $Mail, $Verified, $Password = "") {
            $this->id = $id;
            $this->Username = $Username;
            $this->Mail = $Mail;
            $this->Password = $Password;
            $this->Verified = $Verified;
        }
    }

    /**
    *An entity representing a product
    *@author Leonard Breitkopf
    */
    class Product {
        public $id;
        public $Name;
        public $Description;
        public $Price;
        public $Available;

        /**
        *The constructor, used to automatically set-up the entity object.
        *
        *@param int $id The user's id.
        *@param string $Name The products's username.
        *@param string $Description A product's description.
        *@param double|float $Price The product's price.
        *@param bool $Available This is a value representing an object's stock availability.
        *
        *@access public
        */
        public function __construct($id, $Name, $Description, $Price, $Available) {
            $this->id = $id;
            $this->Name = $Name;
            $this->Description = $Description;
            $this->Price = $Price;
            $this->Available = $Available;
        }
    }
?>
