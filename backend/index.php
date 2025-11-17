<?php

/**
 * Cookboxd REST API - Main Entry Point
 * FlightPHP-based REST API for recipe sharing platform
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers for frontend access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autoload vendor (FlightPHP)
require __DIR__ . '/vendor/autoload.php';

// Load services
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/CategoryService.php';
require_once __DIR__ . '/services/RecipeService.php';
require_once __DIR__ . '/services/IngredientService.php';
require_once __DIR__ . '/services/CommentService.php';

// Load route files
require_once __DIR__ . '/routes/userRoutes.php';
require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/recipeRoutes.php';
require_once __DIR__ . '/routes/ingredientRoutes.php';
require_once __DIR__ . '/routes/commentRoutes.php';

// Initialize FlightPHP
Flight::route('GET /', function() {
    Flight::json([
        'success' => true,
        'message' => 'Cookboxd API v1.0',
        'endpoints' => [
            'users' => '/api/users',
            'categories' => '/api/categories',
            'recipes' => '/api/recipes',
            'ingredients' => '/api/ingredients',
            'comments' => '/api/comments'
        ]
    ]);
});

// 404 handler
Flight::map('notFound', function() {
    Flight::json([
        'success' => false,
        'message' => 'Endpoint not found'
    ], 404);
});

// Error handler
Flight::map('error', function($e) {
    Flight::json([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
});

// Helper function to get JSON input
Flight::map('getJsonInput', function() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
});

// Start the application
Flight::start();
