<?php

require_once __DIR__ . '/../config/database.php';

/**
 * UserDAO
 */
class UserDAO {
    
    private $conn;
    private $table_name = "users";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * CREATE - Add new user
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password_hash, role) 
                  VALUES (:username, :email, :password_hash, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":password_hash", $data['password_hash']);
        $stmt->bindParam(":role", $data['role']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * READ - Get all users
     */
    public function getAll() {
        $query = "SELECT user_id, username, email, role, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get user by ID
     */
    public function getById($id) {
        $query = "SELECT user_id, username, email, role, created_at 
                  FROM " . $this->table_name . " 
                  WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * READ - Get user by email
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * READ - Get user by username
     */
    public function getByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * UPDATE - Update user data
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, 
                      email = :email, 
                      role = :role 
                  WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":role", $data['role']);
        
        return $stmt->execute();
    }
    
    /**
     * UPDATE - Update user password
     */
    public function updatePassword($id, $password_hash) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash 
                  WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":password_hash", $password_hash);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete user
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE email = :email";
        
        if ($exclude_id !== null) {
            $query .= " AND user_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        
        if ($exclude_id !== null) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE username = :username";
        
        if ($exclude_id !== null) {
            $query .= " AND user_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        
        if ($exclude_id !== null) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}
