<?php

/**
 * Ingredient Routes - REST API endpoints for ingredient management
 */

$ingredientService = new IngredientService();

/**
 * @OA\Post(
 *     path="/api/ingredients",
 *     tags={"ingredients"},
 *     summary="Create a new ingredient",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"recipe_id", "name", "quantity"},
 *             @OA\Property(property="recipe_id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Flour"),
 *             @OA\Property(property="quantity", type="string", example="2 cups")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Ingredient added successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('POST /api/ingredients', function() use ($ingredientService) {
    $data = Flight::getJsonInput();
    $result = $ingredientService->createIngredient($data);
    
    $statusCode = $result['success'] ? 201 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Post(
 *     path="/api/ingredients/batch",
 *     tags={"ingredients"},
 *     summary="Create multiple ingredients for a recipe",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"recipe_id", "ingredients"},
 *             @OA\Property(property="recipe_id", type="integer", example=1),
 *             @OA\Property(
 *                 property="ingredients",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="name", type="string", example="Flour"),
 *                     @OA\Property(property="quantity", type="string", example="2 cups")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Ingredients added successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('POST /api/ingredients/batch', function() use ($ingredientService) {
    $data = Flight::getJsonInput();
    
    $recipeId = $data['recipe_id'] ?? 0;
    $ingredients = $data['ingredients'] ?? [];
    
    $result = $ingredientService->createMultipleIngredients($recipeId, $ingredients);
    
    $statusCode = $result['success'] ? 201 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Get(
 *     path="/api/ingredients",
 *     tags={"ingredients"},
 *     summary="Get all ingredients",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all ingredients"
 *     )
 * )
 */
Flight::route('GET /api/ingredients', function() use ($ingredientService) {
    $ingredients = $ingredientService->getAllIngredients();
    
    Flight::json([
        'success' => true,
        'data' => $ingredients
    ]);
});

/**
 * @OA\Get(
 *     path="/api/ingredients/{id}",
 *     tags={"ingredients"},
 *     summary="Get ingredient by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Ingredient ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ingredient details"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ingredient not found"
 *     )
 * )
 */
Flight::route('GET /api/ingredients/@id', function($id) use ($ingredientService) {
    $ingredient = $ingredientService->getIngredientById($id);
    
    if ($ingredient) {
        Flight::json([
            'success' => true,
            'data' => $ingredient
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => 'Ingredient not found'
        ], 404);
    }
});

/**
 * @OA\Get(
 *     path="/api/ingredients/recipe/{recipeId}",
 *     tags={"ingredients"},
 *     summary="Get ingredients by recipe",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of recipe ingredients"
 *     )
 * )
 */
Flight::route('GET /api/ingredients/recipe/@recipeId', function($recipeId) use ($ingredientService) {
    $ingredients = $ingredientService->getIngredientsByRecipeId($recipeId);
    
    Flight::json([
        'success' => true,
        'data' => $ingredients
    ]);
});

/**
 * @OA\Get(
 *     path="/api/ingredients/recipe/{recipeId}/count",
 *     tags={"ingredients"},
 *     summary="Get ingredient count for recipe",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ingredient count"
 *     )
 * )
 */
Flight::route('GET /api/ingredients/recipe/@recipeId/count', function($recipeId) use ($ingredientService) {
    $result = $ingredientService->getRecipeIngredientCount($recipeId);
    
    $statusCode = $result['success'] ? 200 : 404;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Put(
 *     path="/api/ingredients/{id}",
 *     tags={"ingredients"},
 *     summary="Update ingredient",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Ingredient ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="All-Purpose Flour"),
 *             @OA\Property(property="quantity", type="string", example="2.5 cups")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ingredient updated successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('PUT /api/ingredients/@id', function($id) use ($ingredientService) {
    $data = Flight::getJsonInput();
    $result = $ingredientService->updateIngredient($id, $data);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Put(
 *     path="/api/ingredients/recipe/{recipeId}/replace",
 *     tags={"ingredients"},
 *     summary="Replace all ingredients for a recipe",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"ingredients"},
 *             @OA\Property(
 *                 property="ingredients",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="name", type="string", example="Sugar"),
 *                     @OA\Property(property="quantity", type="string", example="1 cup")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recipe ingredients replaced successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('PUT /api/ingredients/recipe/@recipeId/replace', function($recipeId) use ($ingredientService) {
    $data = Flight::getJsonInput();
    $ingredients = $data['ingredients'] ?? [];
    
    $result = $ingredientService->replaceRecipeIngredients($recipeId, $ingredients);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/ingredients/{id}",
 *     tags={"ingredients"},
 *     summary="Delete ingredient",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Ingredient ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ingredient deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Ingredient not found"
 *     )
 * )
 */
Flight::route('DELETE /api/ingredients/@id', function($id) use ($ingredientService) {
    $result = $ingredientService->deleteIngredient($id);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/ingredients/recipe/{recipeId}",
 *     tags={"ingredients"},
 *     summary="Delete all ingredients for a recipe",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="All ingredients deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Recipe not found"
 *     )
 * )
 */
Flight::route('DELETE /api/ingredients/recipe/@recipeId', function($recipeId) use ($ingredientService) {
    $result = $ingredientService->deleteRecipeIngredients($recipeId);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});
