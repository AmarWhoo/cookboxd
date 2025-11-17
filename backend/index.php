<?php

/**
 * Cookboxd Backend API
 * Main entry point for all REST API requests
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS and JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include Router
require_once __DIR__ . '/Router.php';

// Include all route files
require_once __DIR__ . '/routes/userRoutes.php';
require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/recipeRoutes.php';
require_once __DIR__ . '/routes/ingredientRoutes.php';
require_once __DIR__ . '/routes/commentRoutes.php';

// Initialize Router
$router = new Router();

// Add global error handling middleware
$router->before(function() {
    try {
        return true;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'error' => $e->getMessage()
        ]);
        return false;
    }
});

// Register all routes
registerUserRoutes($router);
registerCategoryRoutes($router);
registerRecipeRoutes($router);
registerIngredientRoutes($router);
registerCommentRoutes($router);

// Add a root route for API info
$router->get('/', function() use ($router) {
    $router->json([
        'success' => true,
        'message' => 'Cookboxd API v1.0',
        'endpoints' => [
            'users' => '/users',
            'categories' => '/categories',
            'recipes' => '/recipes',
            'ingredients' => '/ingredients',
            'comments' => '/comments',
            'documentation' => '/docs'
        ]
    ]);
});

// Add API documentation route
$router->get('/docs', function() use ($router) {
    $docs = file_get_contents(__DIR__ . '/docs/openapi.yaml');
    if ($docs) {
        header('Content-Type: text/yaml');
        echo $docs;
    } else {
        $router->json([
            'success' => false,
            'message' => 'Documentation not available'
        ], 404);
    }
});

// Run the router
try {
    $router->run();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
