<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/dao/config.php';
require_once __DIR__ . '/data/Roles.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

require_once __DIR__ . '/rest/services/AuthService.php';
require_once __DIR__ . '/rest/services/UserService.php';
require_once __DIR__ . '/rest/services/CategoryService.php';
require_once __DIR__ . '/rest/services/RecipeService.php';
require_once __DIR__ . '/rest/services/IngredientService.php';
require_once __DIR__ . '/rest/services/CommentService.php';

Flight::register('auth_service', 'AuthService');
Flight::register('auth_middleware', 'AuthMiddleware');
Flight::register('userService', 'UserService');
Flight::register('categoryService', 'CategoryService');
Flight::register('recipeService', 'RecipeService');
Flight::register('ingredientService', 'IngredientService');
Flight::register('commentService', 'CommentService');

// Global middleware - verify JWT token for all routes except auth
Flight::route('/*', function() {
    if (
        strpos(Flight::request()->url, '/auth/login') === 0 ||
        strpos(Flight::request()->url, '/auth/register') === 0
    ) {
        return TRUE;
    } else {
        try {
            $token = Flight::request()->getHeader("Authentication");
            if (!$token)
                Flight::halt(401, "Missing authentication header");

            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));

            Flight::set('user', $decoded_token->user);
            Flight::set('jwt_token', $token);
            return TRUE;
        } catch (\Exception $e) {
            Flight::halt(401, $e->getMessage());
        }
    }
});

require_once __DIR__ . '/rest/routes/AuthRoutes.php';
require_once __DIR__ . '/rest/routes/userRoutes.php';
require_once __DIR__ . '/rest/routes/categoryRoutes.php';
require_once __DIR__ . '/rest/routes/recipeRoutes.php';
require_once __DIR__ . '/rest/routes/ingredientRoutes.php';
require_once __DIR__ . '/rest/routes/commentRoutes.php';

Flight::start();
