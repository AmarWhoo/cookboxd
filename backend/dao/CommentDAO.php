<?php

require_once __DIR__ . '/../config/database.php';

/**
 * CommentDAO
 */
class CommentDAO {
    
    private $conn;
    private $table_name = "comments";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * CREATE - Add new comment
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (recipe_id, user_id, content) 
                  VALUES (:recipe_id, :user_id, :content)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":recipe_id", $data['recipe_id']);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":content", $data['content']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * READ - Get all comments
     */
    public function getAll() {
        $query = "SELECT c.*, u.username, r.title as recipe_title 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.user_id = u.user_id 
                  LEFT JOIN recipes r ON c.recipe_id = r.recipe_id 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get comment by ID
     */
    public function getById($id) {
        $query = "SELECT c.*, u.username, r.title as recipe_title 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.user_id = u.user_id 
                  LEFT JOIN recipes r ON c.recipe_id = r.recipe_id 
                  WHERE c.comment_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * READ - Get comments by recipe ID
     */
    public function getByRecipeId($recipe_id) {
        $query = "SELECT c.*, u.username 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.user_id = u.user_id 
                  WHERE c.recipe_id = :recipe_id 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":recipe_id", $recipe_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * READ - Get comments by user ID
     */
    public function getByUserId($user_id) {
        $query = "SELECT c.*, r.title as recipe_title 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN recipes r ON c.recipe_id = r.recipe_id 
                  WHERE c.user_id = :user_id 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * UPDATE - Update comment
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET content = :content 
                  WHERE comment_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":content", $data['content']);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete comment
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE comment_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete all comments for a recipe
     */
    public function deleteByRecipeId($recipe_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE recipe_id = :recipe_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":recipe_id", $recipe_id);
        
        return $stmt->execute();
    }
    
    /**
     * DELETE - Delete all comments by a user
     */
    public function deleteByUserId($user_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get comment count for a recipe
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
    
    /**
     * Get recent comments with pagination
     */
    public function getRecentPaginated($limit, $offset) {
        $query = "SELECT c.*, u.username, r.title as recipe_title 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.user_id = u.user_id 
                  LEFT JOIN recipes r ON c.recipe_id = r.recipe_id 
                  ORDER BY c.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
