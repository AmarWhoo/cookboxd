<?php

/**
 * Comment Routes - REST API endpoints for comment management
 */

$commentService = new CommentService();

/**
 * @OA\Post(
 *     path="/api/comments",
 *     tags={"comments"},
 *     summary="Create a new comment",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"recipe_id", "user_id", "content"},
 *             @OA\Property(property="recipe_id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="content", type="string", example="Great recipe! Loved it.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Comment posted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('POST /api/comments', function() use ($commentService) {
    $data = Flight::getJsonInput();
    $result = $commentService->createComment($data);
    
    $statusCode = $result['success'] ? 201 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Get(
 *     path="/api/comments",
 *     tags={"comments"},
 *     summary="Get all comments with optional pagination",
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=1),
 *         description="Page number"
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=20),
 *         description="Items per page"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of comments"
 *     )
 * )
 */
Flight::route('GET /api/comments', function() use ($commentService) {
    $page = Flight::request()->query->page ?? null;
    $perPage = Flight::request()->query->per_page ?? null;
    
    if ($page !== null && $perPage !== null) {
        // Paginated results
        $result = $commentService->getRecentComments($page, $perPage);
        Flight::json($result);
    } else {
        // All comments
        $comments = $commentService->getAllComments();
        Flight::json([
            'success' => true,
            'data' => $comments
        ]);
    }
});

/**
 * @OA\Get(
 *     path="/api/comments/{id}",
 *     tags={"comments"},
 *     summary="Get comment by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Comment ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment details"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Comment not found"
 *     )
 * )
 */
Flight::route('GET /api/comments/@id', function($id) use ($commentService) {
    $comment = $commentService->getCommentById($id);
    
    if ($comment) {
        Flight::json([
            'success' => true,
            'data' => $comment
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => 'Comment not found'
        ], 404);
    }
});

/**
 * @OA\Get(
 *     path="/api/comments/recipe/{recipeId}",
 *     tags={"comments"},
 *     summary="Get comments by recipe",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of recipe comments"
 *     )
 * )
 */
Flight::route('GET /api/comments/recipe/@recipeId', function($recipeId) use ($commentService) {
    $comments = $commentService->getCommentsByRecipeId($recipeId);
    
    Flight::json([
        'success' => true,
        'data' => $comments
    ]);
});

/**
 * @OA\Get(
 *     path="/api/comments/recipe/{recipeId}/count",
 *     tags={"comments"},
 *     summary="Get comment count for recipe",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment count"
 *     )
 * )
 */
Flight::route('GET /api/comments/recipe/@recipeId/count', function($recipeId) use ($commentService) {
    $result = $commentService->getRecipeCommentCount($recipeId);
    
    $statusCode = $result['success'] ? 200 : 404;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Get(
 *     path="/api/comments/user/{userId}",
 *     tags={"comments"},
 *     summary="Get comments by user",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of user comments"
 *     )
 * )
 */
Flight::route('GET /api/comments/user/@userId', function($userId) use ($commentService) {
    $comments = $commentService->getCommentsByUserId($userId);
    
    Flight::json([
        'success' => true,
        'data' => $comments
    ]);
});

/**
 * @OA\Put(
 *     path="/api/comments/{id}",
 *     tags={"comments"},
 *     summary="Update comment",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Comment ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "content"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="content", type="string", example="Updated comment text")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment updated successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error or permission denied"
 *     )
 * )
 */
Flight::route('PUT /api/comments/@id', function($id) use ($commentService) {
    $data = Flight::getJsonInput();
    
    // In a real application, userId would come from authentication token
    $userId = $data['user_id'] ?? 0;
    
    $result = $commentService->updateComment($id, $data, $userId);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/comments/{id}",
 *     tags={"comments"},
 *     summary="Delete comment",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Comment ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="is_admin", type="boolean", example=false)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Permission denied or comment not found"
 *     )
 * )
 */
Flight::route('DELETE /api/comments/@id', function($id) use ($commentService) {
    $data = Flight::getJsonInput();
    
    // In a real application, userId and isAdmin would come from authentication token
    $userId = $data['user_id'] ?? 0;
    $isAdmin = $data['is_admin'] ?? false;
    
    $result = $commentService->deleteComment($id, $userId, $isAdmin);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/comments/recipe/{recipeId}",
 *     tags={"comments"},
 *     summary="Delete all comments for a recipe (admin only)",
 *     @OA\Parameter(
 *         name="recipeId",
 *         in="path",
 *         required=true,
 *         description="Recipe ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="All comments deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Recipe not found"
 *     )
 * )
 */
Flight::route('DELETE /api/comments/recipe/@recipeId', function($recipeId) use ($commentService) {
    $result = $commentService->deleteRecipeComments($recipeId);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/comments/user/{userId}",
 *     tags={"comments"},
 *     summary="Delete all comments by a user (admin only)",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="All user comments deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('DELETE /api/comments/user/@userId', function($userId) use ($commentService) {
    $result = $commentService->deleteUserComments($userId);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});
