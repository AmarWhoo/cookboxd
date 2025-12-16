<?php

/**
 * @OA\Get(
 *     path="/comments",
 *     tags={"comments"},
 *     summary="Get all comments",
 *     @OA\Response(response=200, description="Array of comments")
 * )
 */
Flight::route('GET /comments', function() {
    Flight::json(Flight::commentService()->getAll());
});

/**
 * @OA\Get(
 *     path="/comments/{id}",
 *     tags={"comments"},
 *     summary="Get comment by ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Comment object")
 * )
 */
Flight::route('GET /comments/@id', function($id) {
    Flight::json(Flight::commentService()->getById($id));
});

/**
 * @OA\Get(
 *     path="/comments/recipe/{recipeId}",
 *     tags={"comments"},
 *     summary="Get comments by recipe ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="recipeId", required=true),
 *     @OA\Response(response=200, description="Array of comments")
 * )
 */
Flight::route('GET /comments/recipe/@recipeId', function($recipeId) {
    Flight::json(Flight::commentService()->getByRecipeId($recipeId));
});

/**
 * @OA\Get(
 *     path="/comments/user/{userId}",
 *     tags={"comments"},
 *     summary="Get comments by user ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="userId", required=true),
 *     @OA\Response(response=200, description="Array of comments")
 * )
 */
Flight::route('GET /comments/user/@userId', function($userId) {
    Flight::json(Flight::commentService()->getByUserId($userId));
});

/**
 * @OA\Post(
 *     path="/comments",
 *     tags={"comments"},
 *     summary="Create a new comment",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="recipe_id", type="integer"),
 *         @OA\Property(property="user_id", type="integer"),
 *         @OA\Property(property="content", type="string")
 *     )),
 *     @OA\Response(response=200, description="Created comment ID")
 * )
 */
Flight::route('POST /comments', function() {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::USER]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::commentService()->add($data));
});

/**
 * @OA\Put(
 *     path="/comments/{id}",
 *     tags={"comments"},
 *     summary="Update a comment",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="content", type="string")
 *     )),
 *     @OA\Response(response=200, description="Updated comment")
 * )
 */
Flight::route('PUT /comments/@id', function($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::USER]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::commentService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/comments/{id}",
 *     tags={"comments"},
 *     summary="Delete a comment",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Deletion status")
 * )
 */
Flight::route('DELETE /comments/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::commentService()->delete($id));
});
