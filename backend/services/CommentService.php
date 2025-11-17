<?php

require_once __DIR__ . '/../dao/CommentDAO.php';
require_once __DIR__ . '/../dao/RecipeDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';

/**
 * CommentService - Business Logic Layer for Comment Management
 * Handles validation, business rules, and coordinates with CommentDAO
 */
class CommentService {
    
    private $commentDAO;
    private $recipeDAO;
    private $userDAO;
    
    public function __construct() {
        $this->commentDAO = new CommentDAO();
        $this->recipeDAO = new RecipeDAO();
        $this->userDAO = new UserDAO();
    }
    
    /**
     * Create a new comment with validation
     * @param array $data - Comment data
     * @return array - Result with success status and message/data
     */
    public function createComment($data) {
        // Validate comment data
        $validation = $this->validateCommentData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Verify recipe exists
        if (!$this->recipeDAO->getById($data['recipe_id'])) {
            return [
                'success' => false,
                'message' => 'Recipe not found'
            ];
        }
        
        // Verify user exists
        if (!$this->userDAO->getById($data['user_id'])) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Sanitize content
        $data['content'] = trim($data['content']);
        
        // Create comment
        $commentId = $this->commentDAO->create($data);
        
        if ($commentId) {
            return [
                'success' => true,
                'message' => 'Comment posted successfully',
                'comment_id' => $commentId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to post comment'
        ];
    }
    
    /**
     * Get all comments
     * @return array - List of comments
     */
    public function getAllComments() {
        return $this->commentDAO->getAll();
    }
    
    /**
     * Get comment by ID
     * @param int $id - Comment ID
     * @return array|null - Comment data or null
     */
    public function getCommentById($id) {
        if (!$this->isValidId($id)) {
            return null;
        }
        
        return $this->commentDAO->getById($id);
    }
    
    /**
     * Get comments by recipe ID
     * @param int $recipeId - Recipe ID
     * @return array - List of comments
     */
    public function getCommentsByRecipeId($recipeId) {
        if (!$this->isValidId($recipeId)) {
            return [];
        }
        
        return $this->commentDAO->getByRecipeId($recipeId);
    }
    
    /**
     * Get comments by user ID
     * @param int $userId - User ID
     * @return array - List of comments
     */
    public function getCommentsByUserId($userId) {
        if (!$this->isValidId($userId)) {
            return [];
        }
        
        return $this->commentDAO->getByUserId($userId);
    }
    
    /**
     * Get recent comments with pagination
     * @param int $page - Page number (1-indexed)
     * @param int $perPage - Items per page
     * @return array - Result with comments and pagination info
     */
    public function getRecentComments($page = 1, $perPage = 20) {
        // Validate pagination parameters
        $page = max(1, intval($page));
        $perPage = max(1, min(100, intval($perPage))); // Max 100 per page
        
        $offset = ($page - 1) * $perPage;
        
        $comments = $this->commentDAO->getRecentPaginated($perPage, $offset);
        
        return [
            'success' => true,
            'comments' => $comments,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage
            ]
        ];
    }
    
    /**
     * Get comment count for a recipe
     * @param int $recipeId - Recipe ID
     * @return array - Result with count
     */
    public function getRecipeCommentCount($recipeId) {
        if (!$this->isValidId($recipeId)) {
            return [
                'success' => false,
                'message' => 'Invalid recipe ID'
            ];
        }
        
        // Verify recipe exists
        if (!$this->recipeDAO->getById($recipeId)) {
            return [
                'success' => false,
                'message' => 'Recipe not found'
            ];
        }
        
        $count = $this->commentDAO->getCountByRecipeId($recipeId);
        
        return [
            'success' => true,
            'count' => $count
        ];
    }
    
    /**
     * Update comment
     * @param int $id - Comment ID
     * @param array $data - Updated comment data
     * @param int $userId - User ID making the update
     * @return array - Result with success status
     */
    public function updateComment($id, $data, $userId) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid comment ID'
            ];
        }
        
        // Check if comment exists
        $comment = $this->commentDAO->getById($id);
        if (!$comment) {
            return [
                'success' => false,
                'message' => 'Comment not found'
            ];
        }
        
        // Check ownership (user can only edit their own comments)
        if ($comment['user_id'] != $userId) {
            return [
                'success' => false,
                'message' => 'You do not have permission to edit this comment'
            ];
        }
        
        // Validate content
        if (empty($data['content'])) {
            return [
                'success' => false,
                'message' => 'Comment content is required'
            ];
        }
        
        if (strlen($data['content']) < 3) {
            return [
                'success' => false,
                'message' => 'Comment must be at least 3 characters long'
            ];
        }
        
        if (strlen($data['content']) > 2000) {
            return [
                'success' => false,
                'message' => 'Comment cannot exceed 2000 characters'
            ];
        }
        
        // Sanitize content
        $data['content'] = trim($data['content']);
        
        // Update comment
        if ($this->commentDAO->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Comment updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update comment'
        ];
    }
    
    /**
     * Delete comment
     * @param int $id - Comment ID
     * @param int $userId - User ID making the deletion
     * @param bool $isAdmin - Whether user is admin
     * @return array - Result with success status
     */
    public function deleteComment($id, $userId, $isAdmin = false) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid comment ID'
            ];
        }
        
        // Check if comment exists
        $comment = $this->commentDAO->getById($id);
        if (!$comment) {
            return [
                'success' => false,
                'message' => 'Comment not found'
            ];
        }
        
        // Check permission (user can delete their own comments, admin can delete any)
        if (!$isAdmin && $comment['user_id'] != $userId) {
            return [
                'success' => false,
                'message' => 'You do not have permission to delete this comment'
            ];
        }
        
        // Delete comment
        if ($this->commentDAO->delete($id)) {
            return [
                'success' => true,
                'message' => 'Comment deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete comment'
        ];
    }
    
    /**
     * Delete all comments for a recipe (admin only)
     * @param int $recipeId - Recipe ID
     * @return array - Result with success status
     */
    public function deleteRecipeComments($recipeId) {
        if (!$this->isValidId($recipeId)) {
            return [
                'success' => false,
                'message' => 'Invalid recipe ID'
            ];
        }
        
        // Verify recipe exists
        if (!$this->recipeDAO->getById($recipeId)) {
            return [
                'success' => false,
                'message' => 'Recipe not found'
            ];
        }
        
        // Delete all comments for recipe
        if ($this->commentDAO->deleteByRecipeId($recipeId)) {
            return [
                'success' => true,
                'message' => 'All comments deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete comments'
        ];
    }
    
    /**
     * Delete all comments by a user (admin only)
     * @param int $userId - User ID
     * @return array - Result with success status
     */
    public function deleteUserComments($userId) {
        if (!$this->isValidId($userId)) {
            return [
                'success' => false,
                'message' => 'Invalid user ID'
            ];
        }
        
        // Verify user exists
        if (!$this->userDAO->getById($userId)) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Delete all comments by user
        if ($this->commentDAO->deleteByUserId($userId)) {
            return [
                'success' => true,
                'message' => 'All user comments deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete comments'
        ];
    }
    
    /**
     * Validate comment data
     * @param array $data - Comment data to validate
     * @return array - Validation result
     */
    private function validateCommentData($data) {
        // Recipe ID is required
        if (!isset($data['recipe_id']) || empty($data['recipe_id'])) {
            return ['valid' => false, 'message' => 'Recipe ID is required'];
        }
        
        if (!$this->isValidId($data['recipe_id'])) {
            return ['valid' => false, 'message' => 'Invalid recipe ID'];
        }
        
        // User ID is required
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            return ['valid' => false, 'message' => 'User ID is required'];
        }
        
        if (!$this->isValidId($data['user_id'])) {
            return ['valid' => false, 'message' => 'Invalid user ID'];
        }
        
        // Content is required
        if (empty($data['content'])) {
            return ['valid' => false, 'message' => 'Comment content is required'];
        }
        
        // Validate content length
        if (strlen(trim($data['content'])) < 3) {
            return ['valid' => false, 'message' => 'Comment must be at least 3 characters long'];
        }
        
        if (strlen($data['content']) > 2000) {
            return ['valid' => false, 'message' => 'Comment cannot exceed 2000 characters'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate ID format
     * @param mixed $id - ID to validate
     * @return bool - Whether ID is valid
     */
    private function isValidId($id) {
        return is_numeric($id) && $id > 0;
    }
}
