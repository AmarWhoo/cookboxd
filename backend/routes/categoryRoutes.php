<?php

/**
 * Category Routes - REST API endpoints for category management
 */

$categoryService = new CategoryService();

/**
 * @OA\Post(
 *     path="/api/categories",
 *     tags={"categories"},
 *     summary="Create a new category",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Desserts")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Category created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('POST /api/categories', function() use ($categoryService) {
    $data = Flight::getJsonInput();
    $result = $categoryService->createCategory($data);
    
    $statusCode = $result['success'] ? 201 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Get(
 *     path="/api/categories",
 *     tags={"categories"},
 *     summary="Get all categories",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all categories"
 *     )
 * )
 */
Flight::route('GET /api/categories', function() use ($categoryService) {
    $categories = $categoryService->getAllCategories();
    
    Flight::json([
        'success' => true,
        'data' => $categories
    ]);
});

/**
 * @OA\Get(
 *     path="/api/categories/{id}",
 *     tags={"categories"},
 *     summary="Get category by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Category ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category details"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found"
 *     )
 * )
 */
Flight::route('GET /api/categories/@id', function($id) use ($categoryService) {
    $category = $categoryService->getCategoryById($id);
    
    if ($category) {
        Flight::json([
            'success' => true,
            'data' => $category
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => 'Category not found'
        ], 404);
    }
});

/**
 * @OA\Get(
 *     path="/api/categories/{id}/count",
 *     tags={"categories"},
 *     summary="Get recipe count for category",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Category ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recipe count"
 *     )
 * )
 */
Flight::route('GET /api/categories/@id/count', function($id) use ($categoryService) {
    $result = $categoryService->getCategoryRecipeCount($id);
    
    $statusCode = $result['success'] ? 200 : 404;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Put(
 *     path="/api/categories/{id}",
 *     tags={"categories"},
 *     summary="Update category",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Category ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Sweet Desserts")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category updated successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('PUT /api/categories/@id', function($id) use ($categoryService) {
    $data = Flight::getJsonInput();
    $result = $categoryService->updateCategory($id, $data);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/categories/{id}",
 *     tags={"categories"},
 *     summary="Delete category",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Category ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Cannot delete category with recipes"
 *     )
 * )
 */
Flight::route('DELETE /api/categories/@id', function($id) use ($categoryService) {
    $result = $categoryService->deleteCategory($id);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});
