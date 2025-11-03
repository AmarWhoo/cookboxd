<?php

require_once __DIR__ . '/../config/database.php';

/**
 * RecipeDAO
 */
class RecipeDAO {
    
    private $conn;
    private $table_name = "recipes";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * CREATE - Add new recipe
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, category_id, title, description, image_url) 
                  VALUES (:user_id, :category_id, :title, :description, :image_url)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":category_id", $data['category_id']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":image_url", $data['image_url']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * READ - Get all recipes
     */
    public function getAll() {
        $query = "SELECT r.*, u.username, c.name as category_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN users u ON r.user_id = u.user_id 
                  LEFT JOIN categories c ON r.category_id = c.category_id 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get recipe by ID
     */
    public function getById($id) {
        $query = "SELECT r.*, u.username, c.name as category_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN users u ON r.user_id = u.user_id 
                  LEFT JOIN categories c ON r.category_id = c.category_id 
                  WHERE r.recipe_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * READ - Get recipes by user ID
     */
    public function getByUserId($user_id) {
        $query = "SELECT r.*, c.name as category_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN categories c ON r.category_id = c.category_id 
                  WHERE r.user_id = :user_id 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get recipes by category ID
     */
    public function getByCategoryId($category_id) {
        $query = "SELECT r.*, u.username 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN users u ON r.user_id = u.user_id 
                  WHERE r.category_id = :category_id 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Search recipes by title
     */
    public function searchByTitle($search) {
        $query = "SELECT r.*, u.username, c.name as category_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN users u ON r.user_id = u.user_id 
                  LEFT JOIN categories c ON r.category_id = c.category_id 
                  WHERE r.title LIKE :search 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $search_param = "%" . $search . "%";
        $stmt->bindParam(":search", $search_param);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * UPDATE - Update recipe
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_id = :category_id, 
                      title = :title, 
                      description = :description, 
                      image_url = :image_url 
                  WHERE recipe_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":category_id", $data['category_id']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":image_url", $data['image_url']);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete recipe
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE recipe_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Get total recipe count
     */
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Get recipes with pagination
     */
    public function getPaginated($limit, $offset) {
        $query = "SELECT r.*, u.username, c.name as category_name 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN users u ON r.user_id = u.user_id 
                  LEFT JOIN categories c ON r.category_id = c.category_id 
                  ORDER BY r.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
