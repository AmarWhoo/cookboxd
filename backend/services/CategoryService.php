<?php

require_once __DIR__ . '/../dao/CategoryDAO.php';

/**
 * CategoryService - Business Logic Layer for Category Management
 * Handles validation, business rules, and coordinates with CategoryDAO
 */
class CategoryService {
    
    private $categoryDAO;
    
    public function __construct() {
        $this->categoryDAO = new CategoryDAO();
    }
    
    /**
     * Create a new category with validation
     * @param array $data - Category data
     * @return array - Result with success status and message/data
     */
    public function createCategory($data) {
        // Validate category data
        $validation = $this->validateCategoryData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Check if category name already exists
        if ($this->categoryDAO->nameExists($data['name'])) {
            return [
                'success' => false,
                'message' => 'Category name already exists'
            ];
        }
        
        // Create category
        $categoryId = $this->categoryDAO->create($data);
        
        if ($categoryId) {
            return [
                'success' => true,
                'message' => 'Category created successfully',
                'category_id' => $categoryId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to create category'
        ];
    }
    
    /**
     * Get all categories
     * @return array - List of categories
     */
    public function getAllCategories() {
        return $this->categoryDAO->getAll();
    }
    
    /**
     * Get category by ID
     * @param int $id - Category ID
     * @return array|null - Category data or null
     */
    public function getCategoryById($id) {
        if (!$this->isValidId($id)) {
            return null;
        }
        
        return $this->categoryDAO->getById($id);
    }
    
    /**
     * Get category by name
     * @param string $name - Category name
     * @return array|null - Category data or null
     */
    public function getCategoryByName($name) {
        if (empty($name)) {
            return null;
        }
        
        return $this->categoryDAO->getByName($name);
    }
    
    /**
     * Update category
     * @param int $id - Category ID
     * @param array $data - Updated category data
     * @return array - Result with success status
     */
    public function updateCategory($id, $data) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid category ID'
            ];
        }
        
        // Check if category exists
        $category = $this->categoryDAO->getById($id);
        if (!$category) {
            return [
                'success' => false,
                'message' => 'Category not found'
            ];
        }
        
        // Validate update data
        $validation = $this->validateCategoryData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Check name uniqueness if name is being updated
        if (isset($data['name']) && $data['name'] !== $category['name']) {
            if ($this->categoryDAO->nameExists($data['name'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Category name already exists'
                ];
            }
        }
        
        // Update category
        if ($this->categoryDAO->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Category updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update category'
        ];
    }
    
    /**
     * Delete category
     * @param int $id - Category ID
     * @return array - Result with success status
     */
    public function deleteCategory($id) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid category ID'
            ];
        }
        
        // Check if category exists
        if (!$this->categoryDAO->getById($id)) {
            return [
                'success' => false,
                'message' => 'Category not found'
            ];
        }
        
        // Check if category has recipes
        $recipeCount = $this->categoryDAO->getRecipeCount($id);
        if ($recipeCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete category. It is being used by {$recipeCount} recipe(s)"
            ];
        }
        
        // Delete category
        if ($this->categoryDAO->delete($id)) {
            return [
                'success' => true,
                'message' => 'Category deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete category'
        ];
    }
    
    /**
     * Get recipe count for a category
     * @param int $id - Category ID
     * @return array - Result with count
     */
    public function getCategoryRecipeCount($id) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid category ID'
            ];
        }
        
        // Check if category exists
        if (!$this->categoryDAO->getById($id)) {
            return [
                'success' => false,
                'message' => 'Category not found'
            ];
        }
        
        $count = $this->categoryDAO->getRecipeCount($id);
        
        return [
            'success' => true,
            'count' => $count
        ];
    }
    
    /**
     * Validate category data
     * @param array $data - Category data to validate
     * @return array - Validation result
     */
    private function validateCategoryData($data) {
        // Name is required
        if (empty($data['name'])) {
            return ['valid' => false, 'message' => 'Category name is required'];
        }
        
        // Validate name length
        if (strlen($data['name']) < 2) {
            return ['valid' => false, 'message' => 'Category name must be at least 2 characters long'];
        }
        
        if (strlen($data['name']) > 100) {
            return ['valid' => false, 'message' => 'Category name cannot exceed 100 characters'];
        }
        
        // Validate name format (letters, numbers, spaces, hyphens)
        if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $data['name'])) {
            return ['valid' => false, 'message' => 'Category name can only contain letters, numbers, spaces, and hyphens'];
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
