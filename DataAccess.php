<?php
    //Include the dependencies
    require 'Entities.php';
    
    /**
    *Filters to be used with DataAccess.ApplyFilter
    *@abstract
    *@author Leonard Breitkopf
    
    */
    abstract class Filters
    {
        const PRODUCT_NAME = 0;
        const PRICE = 1;
        const AVAILABLE = 2;
    }

    /**
    *Data Access Layer class
    *@author Leonard Breitkopf
    */
    class DataAccess {
        private $PDO;
        private $connected = false;
    
        //DSN Constant
        const DSN = 'mysql:dbname=%s;host=%s;';
    
        //Query Constants
        const QRY_LOGIN = 'SELECT id, Mail, Verified FROM users WHERE users.Username = :username AND users.Password = :password;';
        const QRY_REGISTER = 'INSERT INTO users(Username, Password, Mail) VALUES (:username, :password, :mail);';
        const QRY_GETUSERWNAME = 'SELECT id, Mail, Verified FROM users WHERE users.Username = :username;';
        const QRY_GETUSERWID = 'SELECT Username, Mail, Verified FROM users WHERE users.id = :id;';
        const QRY_ALLPRODUCTS = 'SELECT * FROM products;';
        const QRY_PRODUCT = 'SELECT Name, Beschrijving, Prijs, Voorraad FROM products WHERE id = :id;';
    
        //Error constants
        const ERROR_NOTCONNECTED = 'The SQL client has not yet been connected.';
        const ERROR_QRYNOSUCCESS = 'The execution was unsuccessful or the query didnt return any expected value.';
        const ERROR_ARGUMENTS = 'One or more arguments are invalid.';
    
        /**
        *The constructor, used to auto-connect to the server.
        *
        *@param bool $connect = false This optional parameter states the auto-connection.
        *@param string $server = '' This optional parameter represents the server address or domain name.
        *@param string $database = '' This optional parameter reprensents the database name.
        *@param string $username = '' This optional parameter represents the username used to connect.
        *@param string $password = '' This optional parameter represents the password used to connect.
        *
        *@access public
        */
        public function __construct($connect = false, $server = '', $database = '', $username = '', $password = '') {
            if ($connect)
                $this->connect($server, $database, $username, $password);
        }
    
        /**
        *The destructor, destroys the connection object upon destruction.
        */
        public function __destruct() {
            $this->PDO = null;
        }
    
        /**
        *Used to connect to the MySQL server if not done upon instantiation.
        *
        *@param string $server This parameter represents the server address or domain name.
        *@param string $database This parameter reprensents the database name.
        *@param string $username This parameter represents the username used to connect.
        *@param string $password This parameter represents the password used to connect.
        *
        *@access public
        *@throws PDOException
        */
        public function connect($server, $database, $user, $password) {
            try {
                $this->PDO = new PDO(sprintf(self::DSN, $database, $server), $user, $password);
                $this->connected = true;
            } catch(PDOException $e) {
                throw $e;
            }
        }
    
        /**
        *Used to check if the user has entered valid account information.
        *After checks have completed successfuly returns User object else throws Exception.
        *
        *@param string $username This parameter represents the username used.
        *
        *@param string $password This parameter represents the password used.
        *
        *@return User An entity Object representing the user making the login request.
        *
        *@access public
        *@throws PDOException & Exception
        */
        public function doLogin($username, $password) {
            if ($this->connected) {
                try {
                    $statement = $this->PDO->prepare(QRY_LOGIN);
    
                    if($statement->execute(array(
                                        ':username' => self::sanitizeString($username), 
                                        ':password' => self::hashPassword($password)))) {
                        $result = $statement->fetchAll();
                        if(count($result) > 0)
                            return new User($result[0][0], $username, $result[0][1], $result[0][2]);
                        throw new Exception(self::ERROR_QRYNOSUCCESS);                                          
                    }
                } catch (PDOException $e) {
                    throw $e;
                }
            } else {
                throw new Exception(self::ERROR_NOTCONNECTED);
            }
        }
    
        /**
        *Used to register a new user.
        *After registration completed successfuly returns User object else throws Exception.
        *
        *@param string $username This parameter represents the username used.
        *@param string $password This parameter represents the password used.
        *@param string $email This parameter represents the e-mail address used.
        *
        *@return User An entity Object representing the user making the registration request.
        *
        *@access public
        *@throws PDOException & Exception
        */
        public function registerUser($username, $password, $email) {
            if ($this->connected) {
                try {
                    $statement = $this->PDO->prepare(self::QRY_REGISTER);
                    echo $username .' + ' . $password . ' + ' . $email . '<br />';
                    echo (self::sanitizeString($username) .' + ' . self::hashPassword($password) . ' + ' . self::sanitizeString($email) . '<br />');
                    if($statement->execute(array(
                                    ':username' => self::sanitizeString($username), 
                                    ':password' => self::hashPassword($password), 
                                    ':mail' => self::sanitizeString($email)))) {
                        if ($statement->rowCount() > 0)
                            return true;
                        return false;
                    }
                } catch(PDOException $e) {
                    throw $e;
                }
            } else {
                throw new Exception(self::ERROR_NOTCONNECTED);
            }
        }
    
        /**
        *Used to get a user by it's username
        *After query completed successfuly returns User object else throws Exception.
        *
        *@param string $username This parameter represents the username used to find the user.
        *
        *@return User An entity Object representing the user.
        *
        *@access public
        *@throws PDOException & Exception
        */
        public function getUserByName($username) {
            if ($this->connected) {
                try {
                    $statement = $this->PDO->prepare(self::QRY_GETUSERWNAME);
                    if ($statement->execute(array(
                                     ':username' => self::sanitizeString($username)))) {
                        $result = $statement->fetchAll();
                        if (count($result) > 0) {
                            return new User($result[0][0], $username, $result[0][1], $result[0][2]);
                        } else {
                            throw new Exception(self::ERROR_QRYNOSUCCESS);
                        }
                    } else {
                        throw new Exception(self::ERROR_QRYNOSUCCESS);
                    }
                } catch (PDOException $e) {
                    throw $e;
                }
            } else {
                throw new Exception(self::ERROR_NOTCONNECTED);
            }
        }
    
        /**
        *Used to get a user by it's id.
        *After query completed successfuly returns User object else throws Exception.
        *
        *@param string|int $id This parameter represents the user id used to find the user.
        *
        *@return User An entity Object representing the user.
        *
        *@access public
        *@throws PDOException & Exception
        */
        public function getUserByID($id) {
            if ($this->connected) {
                try {
                    $statement = $this->PDO->prepare(self::QRY_GETUSERWID);
                    if ($statement->execute(array(
                                     ':id' => self::sanitizeString($id)))) {
                        $result = $statement->fetchAll();
                        if (count($result) > 0) {
                            return new User($result[0][0], $username, $result[0][1], $result[0][2]);
                        } else {
                            throw new Exception(self::ERROR_QRYNOSUCCESS);
                        }
                    } else {
                        throw new Exception(self::ERROR_QRYNOSUCCESS);
                    }
                } catch (PDOException $e) {
                    throw $e;
                }
            } else {
                throw new Exception(self::ERROR_NOTCONNECTED);
            }
        }
    
        /**
        *Used to get all the products entered into the products table.
        *After query completed successfuly returns Product array else throws Exception.
        *
        *@return Product[] An array consisting of Product entity objects.
        *
        *@access public
        *@throws PDOException & Exception
        */
        public function getAllProducts() {
            if($this->connected){
                try {
                    $statement = $this->PDO->prepare(self::QRY_ALLPRODUCTS);
                    if ($statement->execute()) {
                        $result = $statement->fetchAll();
                        $return = array();
                        foreach ($result as $row) {
                            array_push($return, new Product($row[0], $row[1], $row[2], $row[3], $row[4]));
                        }
                        return $return;
                    } else {
                       throw new Exception(self::ERROR_QRYNOSUCCESS);
                    }
                } catch(PDOException $e) {
                    throw $e;
                }
            } else {
                throw new Exception(self::ERROR_NOTCONNECTED);
            }
        }
    
        /**
        *Used to return a product through it's ID
        *After query completed successfuly returns User object else throws Exception.
        *
        *@param string|int $id This parameter represents the Product id used to find the product.
        *
        *@return Product An entity Object representing the user.
        *
        *@access public
        *@throws PDOException & Exception
        */
        public function getProduct($id) {
            if($this->connected) {
                try {
                    $statement = $this->PDO->prepare(self::QRY_PRODUCT);
                    if ($statement->execute(array(':id' => self::sanitizeString($id)))) {
                        $result = $statement->fetchAll();
                        if(count($result) > 0) {
                            return new Product($id, $result[0][0], $result[0][1], $result[0][2], $result[0][3]);
                        } else {
                            throw new Exception(self::ERROR_QRYNOSUCCESS);
                        }
                    } else {
                        throw new Exception(self::ERROR_QRYNOSUCCESS);
                    }
                } catch(PDOException $e) {
                    throw $e;
                }
            } else {
                throw new Exception(self::ERROR_NOTCONNECTED);
            }
        }
    
        /**
        *Used to filter an array of products.
        *
        *@param Product[] $array This parameter represents the Product array that the function will filter.
        *@param Filter|int $filter This parameter represents the filter to apply on the array
        *@param mixed $arguments This parameters are the filter arguments, can be an array or a single value
        *
        *@return Product[] The filtered array.
        *
        *@static
        *@access public
        *@throws Exception
        */
        public static function applyFilter($array, $filter, $arguments = '') {
            if ($filter == Filters::PRODUCT_NAME) {
                return self::nameFilter($array, $arguments);
            } elseif ($filter == Filters::PRICE) {
                return self::priceFilter($array, $arguments);
            } elseif ($filter == Filters::AVAILABLE) {
                return self::availableFilter($array);
            } else {
                throw new Exception(self::ERROR_ARGUMENTS);
            }
        }
    
        //Filters the array of products on name
        private static function nameFilter($array, $arguments) {
            $r_array = array();
    
            foreach($array as $entry) {
                if(strpos($entry->Name, $arguments) !== false || $entry->Name == $arguments)
                    array_push($r_array, $entry);
            }
    
            return $r_array;
        }
    
        //Filters the array of products on a price range
        private static function priceFilter($array, $arguments) {
            $r_array = array();
    
            foreach($array as $entry) {
                if($entry->Price >= $arguments[0] && $entry->Price <= $arguments[1])
                    array_push($r_array, $entry);
            }
    
            return $r_array;
        }
    
        //Filters the array of products on availability
        private static function availableFilter($array) {
            $r_array = array();
    
            foreach($array as $entry) {
                if($entry->Available)
                    array_push($r_array, $entry);
            }
    
            return $r_array;
        }
    
        /**
        *Used to sanitize a string, this way we cancel some of the sql inject attempts.
        *
        *@param string $str The string that has to be sanitized.
        *
        *@return string The sanitized string.
        *
        *@static
        *@access public
        */
        public static function sanitizeString($str)
        {
            $str = trim($str);
    
            if (get_magic_quotes_gpc())
                $str = stripslashes($str);
    
            return htmlentities($str);
        }
    
        /**
        *Sanitizes the password and hashes it
        *
        *@param string $password The password that has to be sanitized and hashed.
        *
        *@return string The sanitized and hashed password.
        *
        *@static
        *@access public
        */
        public static function hashPassword($password) {
            return hash('sha384', self::sanitizeString($password));
        }
    }
?>