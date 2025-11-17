<?php

/**
 * Recipe Routes - REST API endpoints for recipe management
 */

$recipeService = new RecipeService();

/**
 * @OA\Post(
 *     path="/api/recipes",
 *     tags={"recipes"},
 *     summary="Create a new recipe",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "title"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="category_id", type="integer", example=2),
 *             @OA\Property(property="title", type="string", example="Chocolate Cake"),
 *             @OA\Property(property="description", type="string", example="Delicious chocolate cake recipe"),
 *             @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Recipe created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('POST /api/recipes', function() use ($recipeService) {
    $data = Flight::getJsonInput();
    $result = $recipeService->createRecipe($data);
    
    $statusCode = $result['success'] ? 201 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Get(
 *     path="/api/recipes",
 *     tags={"recipes"},
 *     summary="Get all recipes with optional pagination",
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=1),
 *         description="Page number for pagination"
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=10),
 *         description="Items per page"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of recipes"
 *     )
 * )
 */
Flight::route('GET /api/recipes', function() use ($recipeService) {
    $page = Flight::request()->query->page ?? null;
    $perPage = Flight::request()->query->per_page ?? null;
    
    if ($page !== null && $perPage !== null) {
        // Paginated results
        $result = $recipeService->getPaginatedRecipes($page, $perPage);
        Flight::json($result);
    } else {
        // All recipes
        $recipes = $recipeService->getAllRecipes();
        Flight::json([
            'success' => true,
            'data' => $recipes
        ]);
    }
});

/**
 * @OA\Get(
 *     path="/api/recipes/search",
 *     tags={"recipes"},
 *     summary="Search recipes by title",
 *     @OA\Parameter(
 *         name="q",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="chocolate"),
 *         description="Search query"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of matching recipes"
 *     )
 * )
 */
Flight::route('GET /api/recipes/search', function() use ($recipeService) {
    $query = Flight::request()->query->q ?? '';
    $recipes = $recipeService->searchRecipes($query);
    
    Flight::json([
        'success' => true,
        'data' => $recipes
    ]);
});

/**
 * @OA\Get(
 *     path="/api/recipes/user/{userId}",
 *     tags={"recipes"},
 *     summary="Get recipes by user",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of user's recipes"
 *     )
 * )
 */
Flight::route('GET /api/recipes/user/@userId', function($userId) use ($recipeService) {
    $recipes = $recipeService->getRecipesByUserId($userId);
    
    Flight::json([
        'success' => true,
        'data' => $recipes
    ]);
});

/**
 * @OA\Get(
 *     path="/api/recipes/category/{categoryId}",
 *     tags={"recipes"},
 *     summary="Get recipes by category",
 *     @OA\Parameter(
 *         name="categoryId",
 *         in="path",
 *         required=true,
 *         description="Category ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of recipes in category"
 *     )
 * )
 */
Flight::route('GET /api/recipes/category/@categoryId', function($categoryId) use ($recipeService) {
    $recipes = $recipeService->getRecipesByCategoryId($categoryId);
    
    Flight::json([
        'success' => true,
        'data' => $recipes
    ]);
});

/**
 * @OA\Get(
 *     path="/api/recipes/{id}",
 *     tags={"recipes"},
 *     summary="Get recipe by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recipe details"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Recipe not found"
 *     )
 * )
 */
Flight::route('GET /api/recipes/@id', function($id) use ($recipeService) {
    $recipe = $recipeService->getRecipeById($id);
    
    if ($recipe) {
        Flight::json([
            'success' => true,
            'data' => $recipe
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => 'Recipe not found'
        ], 404);
    }
});

/**
 * @OA\Put(
 *     path="/api/recipes/{id}",
 *     tags={"recipes"},
 *     summary="Update recipe",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="title", type="string", example="Updated Recipe Title"),
 *             @OA\Property(property="description", type="string", example="Updated description"),
 *             @OA\Property(property="category_id", type="integer", example=2),
 *             @OA\Property(property="image_url", type="string", example="https://example.com/new-image.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recipe updated successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error or permission denied"
 *     )
 * )
 */
Flight::route('PUT /api/recipes/@id', function($id) use ($recipeService) {
    $data = Flight::getJsonInput();
    
    // In a real application, userId would come from authentication token
    $userId = $data['user_id'] ?? 0;
    
    $result = $recipeService->updateRecipe($id, $data, $userId);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/recipes/{id}",
 *     tags={"recipes"},
 *     summary="Delete recipe",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id"},
 *             @OA\Property(property="user_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recipe deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Permission denied or recipe not found"
 *     )
 * )
 */
Flight::route('DELETE /api/recipes/@id', function($id) use ($recipeService) {
    $data = Flight::getJsonInput();
    
    // In a real application, userId would come from authentication token
    $userId = $data['user_id'] ?? 0;
    
    $result = $recipeService->deleteRecipe($id, $userId);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});
