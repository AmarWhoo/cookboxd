<?php

require_once __DIR__ . '/../dao/UserDAO.php';

/**
 * UserService - Business Logic Layer for User Management
 * Handles validation, business rules, and coordinates with UserDAO
 */
class UserService {
    
    private $userDAO;
    
    public function __construct() {
        $this->userDAO = new UserDAO();
    }
    
    /**
     * Register a new user with validation
     * @param array $data - User registration data
     * @return array - Result with success status and message/data
     */
    public function registerUser($data) {
        // Validate required fields
        $validation = $this->validateUserData($data, true);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Check if email already exists
        if ($this->userDAO->emailExists($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email address is already registered'
            ];
        }
        
        // Check if username already exists
        if ($this->userDAO->usernameExists($data['username'])) {
            return [
                'success' => false,
                'message' => 'Username is already taken'
            ];
        }
        
        // Hash password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        // Create user
        $userId = $this->userDAO->create($data);
        
        if ($userId) {
            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to register user'
        ];
    }
    
    /**
     * Authenticate user login
     * @param string $login - Email or username
     * @param string $password - Plain text password
     * @return array - Result with success status and user data
     */
    public function loginUser($login, $password) {
        // Validate inputs
        if (empty($login) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email/username and password are required'
            ];
        }
        
        // Try to find user by email or username
        $user = filter_var($login, FILTER_VALIDATE_EMAIL) 
            ? $this->userDAO->getByEmail($login) 
            : $this->userDAO->getByUsername($login);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    }
    
    /**
     * Get all users (admin only)
     * @return array - List of users
     */
    public function getAllUsers() {
        return $this->userDAO->getAll();
    }
    
    /**
     * Get user by ID
     * @param int $id - User ID
     * @return array|null - User data or null
     */
    public function getUserById($id) {
        if (!$this->isValidId($id)) {
            return null;
        }
        
        return $this->userDAO->getById($id);
    }
    
    /**
     * Update user profile
     * @param int $id - User ID
     * @param array $data - Updated user data
     * @return array - Result with success status
     */
    public function updateUser($id, $data) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid user ID'
            ];
        }
        
        // Check if user exists
        $user = $this->userDAO->getById($id);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Validate update data
        $validation = $this->validateUserData($data, false);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        // Check email uniqueness if email is being updated
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if ($this->userDAO->emailExists($data['email'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Email address is already in use'
                ];
            }
        }
        
        // Check username uniqueness if username is being updated
        if (isset($data['username']) && $data['username'] !== $user['username']) {
            if ($this->userDAO->usernameExists($data['username'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Username is already taken'
                ];
            }
        }
        
        // Update user
        if ($this->userDAO->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update user'
        ];
    }
    
    /**
     * Change user password
     * @param int $id - User ID
     * @param string $currentPassword - Current password
     * @param string $newPassword - New password
     * @return array - Result with success status
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid user ID'
            ];
        }
        
        // Get user with password hash
        $user = $this->userDAO->getByEmail($this->userDAO->getById($id)['email']);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }
        
        // Validate new password
        $passwordValidation = $this->validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            return [
                'success' => false,
                'message' => $passwordValidation['message']
            ];
        }
        
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        if ($this->userDAO->updatePassword($id, $newPasswordHash)) {
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to change password'
        ];
    }
    
    /**
     * Delete user account
     * @param int $id - User ID
     * @return array - Result with success status
     */
    public function deleteUser($id) {
        if (!$this->isValidId($id)) {
            return [
                'success' => false,
                'message' => 'Invalid user ID'
            ];
        }
        
        // Check if user exists
        if (!$this->userDAO->getById($id)) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Delete user (cascades to recipes and comments)
        if ($this->userDAO->delete($id)) {
            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete user'
        ];
    }
    
    /**
     * Validate user data
     * @param array $data - User data to validate
     * @param bool $isNew - Whether this is a new user (requires password)
     * @return array - Validation result
     */
    private function validateUserData($data, $isNew = false) {
        // Required fields for new users
        if ($isNew) {
            if (empty($data['username'])) {
                return ['valid' => false, 'message' => 'Username is required'];
            }
            
            if (empty($data['email'])) {
                return ['valid' => false, 'message' => 'Email is required'];
            }
            
            if (empty($data['password'])) {
                return ['valid' => false, 'message' => 'Password is required'];
            }
            
            // Validate password strength
            $passwordValidation = $this->validatePassword($data['password']);
            if (!$passwordValidation['valid']) {
                return $passwordValidation;
            }
        }
        
        // Validate username format (if provided)
        if (isset($data['username'])) {
            if (strlen($data['username']) < 3) {
                return ['valid' => false, 'message' => 'Username must be at least 3 characters long'];
            }
            
            if (strlen($data['username']) > 50) {
                return ['valid' => false, 'message' => 'Username cannot exceed 50 characters'];
            }
            
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores'];
            }
        }
        
        // Validate email format (if provided)
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        // Validate role (if provided)
        if (isset($data['role']) && !in_array($data['role'], ['user', 'admin'])) {
            return ['valid' => false, 'message' => 'Invalid role. Must be "user" or "admin"'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate password strength
     * @param string $password - Password to validate
     * @return array - Validation result
     */
    private function validatePassword($password) {
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
        }
        
        if (strlen($password) > 255) {
            return ['valid' => false, 'message' => 'Password is too long'];
        }
        
        // Check for at least one letter and one number
        if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one letter and one number'];
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
