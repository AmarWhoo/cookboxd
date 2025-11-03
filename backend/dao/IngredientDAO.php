<?php

require_once __DIR__ . '/../config/database.php';

/**
 * IngredientDAO
 */
class IngredientDAO {
    
    private $conn;
    private $table_name = "ingredients";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * CREATE - Add new ingredient
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (recipe_id, name, quantity) 
                  VALUES (:recipe_id, :name, :quantity)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":recipe_id", $data['recipe_id']);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":quantity", $data['quantity']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * CREATE - Add multiple ingredients for a recipe
     */
    public function createMultiple($recipe_id, $ingredients) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($ingredients as $ingredient) {
                $data = [
                    'recipe_id' => $recipe_id,
                    'name' => $ingredient['name'],
                    'quantity' => $ingredient['quantity']
                ];
                
                if (!$this->create($data)) {
                    throw new Exception("Failed to insert ingredient");
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * READ - Get all ingredients
     */
    public function getAll() {
        $query = "SELECT i.*, r.title as recipe_title 
                  FROM " . $this->table_name . " i 
                  LEFT JOIN recipes r ON i.recipe_id = r.recipe_id 
                  ORDER BY i.recipe_id, i.ingredient_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get ingredient by ID
     */
    public function getById($id) {
        $query = "SELECT i.*, r.title as recipe_title 
                  FROM " . $this->table_name . " i 
                  LEFT JOIN recipes r ON i.recipe_id = r.recipe_id 
                  WHERE i.ingredient_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * READ - Get ingredients by recipe ID
     */
    public function getByRecipeId($recipe_id) {
        $query = "SELECT ingredient_id, name, quantity 
                  FROM " . $this->table_name . " 
                  WHERE recipe_id = :recipe_id 
                  ORDER BY ingredient_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":recipe_id", $recipe_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * UPDATE - Update ingredient
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      quantity = :quantity 
                  WHERE ingredient_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":quantity", $data['quantity']);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete ingredient
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE ingredient_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete all ingredients for a recipe
     */
    public function deleteByRecipeId($recipe_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE recipe_id = :recipe_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":recipe_id", $recipe_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get ingredient count for a recipe
     */
    public function getCountByRecipeId($recipe_id) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  WHERE recipe_id = :recipe_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":recipe_id", $recipe_id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }
}
