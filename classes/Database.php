<?php
require 'config.php';

class Database {
    
    //Basic db connection, change credential according to server configurations.
    private $db_host; 
    private $db_name;
    private $db_username;
    private $db_password;
    public  $url;
    
    public function __construct($create = false) {
        $params = config::params();
        $this->db_host = $params['db_host'];
        $this->db_name = $params['db_name'];
        $this->db_username = $params['db_username'];
        $this->db_password = $params['db_password'];
        $this->url = $params['url'];
        // Check if database exists
        if($create){
            $conn = new PDO("mysql:host=$this->db_host", $this->db_username, $this->db_password);
            // Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Check if database exists
            $stmt = $conn->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname");
            $stmt->bindParam(':dbname', $this->db_name);
            $stmt->execute();
             if (!$stmt->rowCount()) {
                // Create database
                $stmt = $conn->prepare("CREATE DATABASE $this->db_name");
                $stmt->execute();
                 echo "Database created successfully \n";
              }
        }
    }
    /**
     * Public function to create database connection 
     * @return Resource \PDO
     * @author Sudhanshu Saxena <marjss21@gmail.com>
     */
    public function dbConnection(){
        
        try{
            $conn = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name,$this->db_username,$this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e){
            echo "Connection error ".$e->getMessage(); 
            exit;
        }
          
    }
}