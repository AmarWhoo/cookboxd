<?php

require_once __DIR__ . '/../dao/IngredientDAO.php';
require_once __DIR__ . '/../dao/RecipeDAO.php';

/**
 * IngredientService - Business Logic Layer for Ingredient Management
 * Handles validation, business rules, and coordinates with IngredientDAO
 */
class IngredientService {
    
    private $ingredientDAO;
    private $recipeDAO;
    
    public function __construct() {
        $this->ingredientDAO = new IngredientDAO();
        $this->recipeDAO = new RecipeDAO();
    }
    
    /**
     * Create a new ingredient with validation
     * @param array $data - Ingredient data
     * @return array - Result with success status and message/data
     */
    public function createIngredient($data) {
        // Validate ingredient data
        $validation = $this->validateIngredientData($data);
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
        
        // Create ingredient
        $ingredientId = $this->ingredientDAO->create($data);
        
        if ($ingredientId) {
            return [
                'success' => true,
                'message' => 'Ingredient added successfully',
                'ingredient_id' => $ingredientId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to add ingredient'
        ];
    }
    
    /**
     * Create multiple ingredients for a recipe
     * @param int $recipeId - Recipe ID
     * @param array $ingredients - Array of ingredient data
     * @return array - Result with success status
     */
    public function createMultipleIngredients($recipeId, $ingredients) {
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
        
        // Validate ingredients array
        if (!is_array($ingredients) || empty($ingredients)) {
            return [
                'success' => false,
                'message' => 'Ingredients array is required and cannot be empty'
            ];
        }
        
        // Validate each ingredient
        foreach ($ingredients as $index => $ingredient) {
            // Add recipe_id to each ingredient
            $ingredient['recipe_id'] = $recipeId;
            
            $validation = $this->validateIngredientData($ingredient);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => "Ingredient #" . ($index + 1) . ": " . $validation['message']
                ];
            }
        }
        
        // Create all ingredients
        if ($this->ingredientDAO->createMultiple($recipeId, $ingredients)) {
            return [
                'success' => true,
                'message' => count($ingredients) . ' ingredients added successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to add ingredients'
        ];
    }
    
    /**
     * Get all ingredients
     * @return array - List of ingredients
     */
    public function getAllIngredients() {
        return $this->ingredientDAO->getAll();
    }
    
    /**
     * Get ingredient by ID
     * @param int $id - Ingredient ID
     * @return array|null - Ingredient data or null
     */
    public function getIngredientById($id) {
        if (!$this->isValidId($id)) {
            return null;
        }
        
        return $this->ingredientDAO->getById($id);
    }
    
    /**
     * Get ingredients by recipe ID
     * @param int $recipeId - Recipe ID
     * @return array - List of ingredients
     */
    public function getIngredientsByRecipeId($recipeId) {
        if (!$this->isValidId($recipeId)) {
            return [];
        }
        
        return $this->ingredientDAO->getByRecipeId($recipeId);
    }
    
    /**
     * Get ingredient count for a recipe
     * @param int $recipeId - Recipe ID
     * @return array - Result with count
     */
    public function getRecipeIngredientCount($recipeId) {
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
        
        $count = $this->ingredientDAO->getCountByRecipeId($recipeId);
        
        return [
            'success' => true,
            'count' => $count
        ];
    }
    
    /**
     * Update ingredient
     * @param int $id - Ingredient ID
     * @param array $data - Updated ingredient data
     * @return array - Result with success status
     */
    public function updateIngredient($id, $data) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid ingredient ID'
            ];
        }
        
        // Check if ingredient exists
        $ingredient = $this->ingredientDAO->getById($id);
        if (!$ingredient) {
            return [
                'success' => false,
                'message' => 'Ingredient not found'
            ];
        }
        
        // Validate update data (partial validation)
        if (isset($data['name'])) {
            if (empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'Ingredient name cannot be empty'
                ];
            }
            
            if (strlen($data['name']) > 255) {
                return [
                    'success' => false,
                    'message' => 'Ingredient name cannot exceed 255 characters'
                ];
            }
        }
        
        if (isset($data['quantity'])) {
            if (empty($data['quantity'])) {
                return [
                    'success' => false,
                    'message' => 'Ingredient quantity cannot be empty'
                ];
            }
            
            if (strlen($data['quantity']) > 100) {
                return [
                    'success' => false,
                    'message' => 'Ingredient quantity cannot exceed 100 characters'
                ];
            }
        }
        
        // Update ingredient
        if ($this->ingredientDAO->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Ingredient updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update ingredient'
        ];
    }
    
    /**
     * Delete ingredient
     * @param int $id - Ingredient ID
     * @return array - Result with success status
     */
    public function deleteIngredient($id) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid ingredient ID'
            ];
        }
        
        // Check if ingredient exists
        if (!$this->ingredientDAO->getById($id)) {
            return [
                'success' => false,
                'message' => 'Ingredient not found'
            ];
        }
        
        // Delete ingredient
        if ($this->ingredientDAO->delete($id)) {
            return [
                'success' => true,
                'message' => 'Ingredient deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete ingredient'
        ];
    }
    
    /**
     * Delete all ingredients for a recipe
     * @param int $recipeId - Recipe ID
     * @return array - Result with success status
     */
    public function deleteRecipeIngredients($recipeId) {
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
        
        // Delete all ingredients for recipe
        if ($this->ingredientDAO->deleteByRecipeId($recipeId)) {
            return [
                'success' => true,
                'message' => 'All ingredients deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete ingredients'
        ];
    }
    
    /**
     * Replace all ingredients for a recipe
     * @param int $recipeId - Recipe ID
     * @param array $ingredients - New ingredients array
     * @return array - Result with success status
     */
    public function replaceRecipeIngredients($recipeId, $ingredients) {
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
        
        // Validate ingredients array
        if (!is_array($ingredients) || empty($ingredients)) {
            return [
                'success' => false,
                'message' => 'Ingredients array is required and cannot be empty'
            ];
        }
        
        // Validate each ingredient
        foreach ($ingredients as $index => $ingredient) {
            $ingredient['recipe_id'] = $recipeId;
            
            $validation = $this->validateIngredientData($ingredient);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => "Ingredient #" . ($index + 1) . ": " . $validation['message']
                ];
            }
        }
        
        // Delete existing ingredients
        $this->ingredientDAO->deleteByRecipeId($recipeId);
        
        // Create new ingredients
        if ($this->ingredientDAO->createMultiple($recipeId, $ingredients)) {
            return [
                'success' => true,
                'message' => 'Recipe ingredients replaced successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to replace ingredients'
        ];
    }
    
    /**
     * Validate ingredient data
     * @param array $data - Ingredient data to validate
     * @return array - Validation result
     */
    private function validateIngredientData($data) {
        // Recipe ID is required
        if (!isset($data['recipe_id']) || empty($data['recipe_id'])) {
            return ['valid' => false, 'message' => 'Recipe ID is required'];
        }
        
        if (!$this->isValidId($data['recipe_id'])) {
            return ['valid' => false, 'message' => 'Invalid recipe ID'];
        }
        
        // Name is required
        if (empty($data['name'])) {
            return ['valid' => false, 'message' => 'Ingredient name is required'];
        }
        
        if (strlen($data['name']) > 255) {
            return ['valid' => false, 'message' => 'Ingredient name cannot exceed 255 characters'];
        }
        
        // Quantity is required
        if (empty($data['quantity'])) {
            return ['valid' => false, 'message' => 'Ingredient quantity is required'];
        }
        
        if (strlen($data['quantity']) > 100) {
            return ['valid' => false, 'message' => 'Ingredient quantity cannot exceed 100 characters'];
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
