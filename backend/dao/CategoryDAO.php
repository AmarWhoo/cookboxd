<?php

require_once __DIR__ . '/../config/database.php';

/**
 * CategoryDAO
 */
class CategoryDAO {
    
    private $conn;
    private $table_name = "categories";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * CREATE - Add new category
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name) 
                  VALUES (:name)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $data['name']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * READ - Get all categories
     */
    public function getAll() {
        $query = "SELECT category_id, name 
                  FROM " . $this->table_name . " 
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get category by ID
     */
    public function getById($id) {
        $query = "SELECT category_id, name 
                  FROM " . $this->table_name . " 
                  WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * READ - Get category by name
     */
    public function getByName($name) {
        $query = "SELECT category_id, name 
                  FROM " . $this->table_name . " 
                  WHERE name = :name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * UPDATE - Update category
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name 
                  WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $data['name']);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete category
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Get recipe count for a category
     */
    public function getRecipeCount($id) {
        $query = "SELECT COUNT(*) as count 
                  FROM recipes 
                  WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Check if category name exists
     */
    public function nameExists($name, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE name = :name";
        
        if ($exclude_id !== null) {
            $query .= " AND category_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        
        if ($exclude_id !== null) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}
