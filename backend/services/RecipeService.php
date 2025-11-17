<?php

require_once __DIR__ . '/../dao/RecipeDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/../dao/CategoryDAO.php';

/**
 * RecipeService - Business Logic Layer for Recipe Management
 * Handles validation, business rules, and coordinates with RecipeDAO
 */
class RecipeService {
    
    private $recipeDAO;
    private $userDAO;
    private $categoryDAO;
    
    public function __construct() {
        $this->recipeDAO = new RecipeDAO();
        $this->userDAO = new UserDAO();
        $this->categoryDAO = new CategoryDAO();
    }
    
    /**
     * Create a new recipe with validation
     * @param array $data - Recipe data
     * @return array - Result with success status and message/data
     */
    public function createRecipe($data) {
        // Validate recipe data
        $validation = $this->validateRecipeData($data, true);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Verify user exists
        if (!$this->userDAO->getById($data['user_id'])) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Verify category exists (if provided)
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            if (!$this->categoryDAO->getById($data['category_id'])) {
                return [
                    'success' => false,
                    'message' => 'Category not found'
                ];
            }
        }
        
        // Set default values
        if (!isset($data['image_url']) || empty($data['image_url'])) {
            $data['image_url'] = null;
        }
        
        // Create recipe
        $recipeId = $this->recipeDAO->create($data);
        
        if ($recipeId) {
            return [
                'success' => true,
                'message' => 'Recipe created successfully',
                'recipe_id' => $recipeId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to create recipe'
        ];
    }
    
    /**
     * Get all recipes
     * @return array - List of recipes
     */
    public function getAllRecipes() {
        return $this->recipeDAO->getAll();
    }
    
    /**
     * Get recipe by ID
     * @param int $id - Recipe ID
     * @return array|null - Recipe data or null
     */
    public function getRecipeById($id) {
        if (!$this->isValidId($id)) {
            return null;
        }
        
        return $this->recipeDAO->getById($id);
    }
    
    /**
     * Get recipes by user ID
     * @param int $userId - User ID
     * @return array - List of recipes
     */
    public function getRecipesByUserId($userId) {
        if (!$this->isValidId($userId)) {
            return [];
        }
        
        return $this->recipeDAO->getByUserId($userId);
    }
    
    /**
     * Get recipes by category ID
     * @param int $categoryId - Category ID
     * @return array - List of recipes
     */
    public function getRecipesByCategoryId($categoryId) {
        if (!$this->isValidId($categoryId)) {
            return [];
        }
        
        return $this->recipeDAO->getByCategoryId($categoryId);
    }
    
    /**
     * Search recipes by title
     * @param string $search - Search term
     * @return array - List of matching recipes
     */
    public function searchRecipes($search) {
        if (empty($search)) {
            return [];
        }
        
        // Sanitize search term
        $search = trim($search);
        
        if (strlen($search) < 2) {
            return [];
        }
        
        return $this->recipeDAO->searchByTitle($search);
    }
    
    /**
     * Get paginated recipes
     * @param int $page - Page number (1-indexed)
     * @param int $perPage - Items per page
     * @return array - Result with recipes and pagination info
     */
    public function getPaginatedRecipes($page = 1, $perPage = 10) {
        // Validate pagination parameters
        $page = max(1, intval($page));
        $perPage = max(1, min(100, intval($perPage))); // Max 100 per page
        
        $offset = ($page - 1) * $perPage;
        
        $recipes = $this->recipeDAO->getPaginated($perPage, $offset);
        $totalCount = $this->recipeDAO->getTotalCount();
        $totalPages = ceil($totalCount / $perPage);
        
        return [
            'success' => true,
            'recipes' => $recipes,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalCount,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1
            ]
        ];
    }
    
    /**
     * Update recipe
     * @param int $id - Recipe ID
     * @param array $data - Updated recipe data
     * @param int $userId - User ID making the update
     * @return array - Result with success status
     */
    public function updateRecipe($id, $data, $userId) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid recipe ID'
            ];
        }
        
        // Check if recipe exists
        $recipe = $this->recipeDAO->getById($id);
        if (!$recipe) {
            return [
                'success' => false,
                'message' => 'Recipe not found'
            ];
        }
        
        // Check ownership (user can only edit their own recipes unless admin)
        if ($recipe['user_id'] != $userId) {
            return [
                'success' => false,
                'message' => 'You do not have permission to edit this recipe'
            ];
        }
        
        // Validate update data
        $validation = $this->validateRecipeData($data, false);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Verify category exists (if being updated)
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            if (!$this->categoryDAO->getById($data['category_id'])) {
                return [
                    'success' => false,
                    'message' => 'Category not found'
                ];
            }
        }
        
        // Update recipe
        if ($this->recipeDAO->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Recipe updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update recipe'
        ];
    }
    
    /**
     * Delete recipe
     * @param int $id - Recipe ID
     * @param int $userId - User ID making the deletion
     * @return array - Result with success status
     */
    public function deleteRecipe($id, $userId) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid recipe ID'
            ];
        }
        
        // Check if recipe exists
        $recipe = $this->recipeDAO->getById($id);
        if (!$recipe) {
            return [
                'success' => false,
                'message' => 'Recipe not found'
            ];
        }
        
        // Check ownership (user can only delete their own recipes unless admin)
        if ($recipe['user_id'] != $userId) {
            return [
                'success' => false,
                'message' => 'You do not have permission to delete this recipe'
            ];
        }
        
        // Delete recipe (cascades to ingredients and comments)
        if ($this->recipeDAO->delete($id)) {
            return [
                'success' => true,
                'message' => 'Recipe deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete recipe'
        ];
    }
    
    /**
     * Validate recipe data
     * @param array $data - Recipe data to validate
     * @param bool $isNew - Whether this is a new recipe
     * @return array - Validation result
     */
    private function validateRecipeData($data, $isNew = false) {
        // Required fields for new recipes
        if ($isNew) {
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                return ['valid' => false, 'message' => 'User ID is required'];
            }
            
            if (!$this->isValidId($data['user_id'])) {
                return ['valid' => false, 'message' => 'Invalid user ID'];
            }
            
            if (empty($data['title'])) {
                return ['valid' => false, 'message' => 'Recipe title is required'];
            }
        }
        
        // Validate title (if provided)
        if (isset($data['title'])) {
            if (strlen($data['title']) < 3) {
                return ['valid' => false, 'message' => 'Recipe title must be at least 3 characters long'];
            }
            
            if (strlen($data['title']) > 255) {
                return ['valid' => false, 'message' => 'Recipe title cannot exceed 255 characters'];
            }
        }
        
        // Validate description (if provided)
        if (isset($data['description']) && !empty($data['description'])) {
            if (strlen($data['description']) < 10) {
                return ['valid' => false, 'message' => 'Recipe description must be at least 10 characters long'];
            }
        }
        
        // Validate image URL (if provided)
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            if (strlen($data['image_url']) > 500) {
                return ['valid' => false, 'message' => 'Image URL is too long'];
            }
            
            // Basic URL validation
            if (!filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
                return ['valid' => false, 'message' => 'Invalid image URL format'];
            }
        }
        
        // Validate category_id (if provided)
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            if (!$this->isValidId($data['category_id'])) {
                return ['valid' => false, 'message' => 'Invalid category ID'];
            }
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
