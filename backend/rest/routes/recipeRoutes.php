<?php

/**
 * @OA\Get(
 *     path="/recipes",
 *     tags={"recipes"},
 *     summary="Get all recipes",
 *     @OA\Response(response=200, description="Array of recipes")
 * )
 */
Flight::route('GET /recipes', function() {
    Flight::json(Flight::recipeService()->getAll());
});

/**
 * @OA\Get(
 *     path="/recipes/{id}",
 *     tags={"recipes"},
 *     summary="Get recipe by ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Recipe object")
 * )
 */
Flight::route('GET /recipes/@id', function($id) {
    Flight::json(Flight::recipeService()->getById($id));
});

/**
 * @OA\Get(
 *     path="/recipes/user/{userId}",
 *     tags={"recipes"},
 *     summary="Get recipes by user ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="userId", required=true),
 *     @OA\Response(response=200, description="Array of recipes")
 * )
 */
Flight::route('GET /recipes/user/@userId', function($userId) {
    Flight::json(Flight::recipeService()->getByUserId($userId));
});

/**
 * @OA\Get(
 *     path="/recipes/category/{categoryId}",
 *     tags={"recipes"},
 *     summary="Get recipes by category ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="categoryId", required=true),
 *     @OA\Response(response=200, description="Array of recipes")
 * )
 */
Flight::route('GET /recipes/category/@categoryId', function($categoryId) {
    Flight::json(Flight::recipeService()->getByCategoryId($categoryId));
});

/**
 * @OA\Post(
 *     path="/recipes",
 *     tags={"recipes"},
 *     summary="Create a new recipe",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="user_id", type="integer"),
 *         @OA\Property(property="category_id", type="integer"),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="instructions", type="string")
 *     )),
 *     @OA\Response(response=200, description="Created recipe ID")
 * )
 */
Flight::route('POST /recipes', function() {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::USER]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::recipeService()->add($data));
});

/**
 * @OA\Put(
 *     path="/recipes/{id}",
 *     tags={"recipes"},
 *     summary="Update a recipe",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="instructions", type="string")
 *     )),
 *     @OA\Response(response=200, description="Updated recipe")
 * )
 */
Flight::route('PUT /recipes/@id', function($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::USER]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::recipeService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/recipes/{id}",
 *     tags={"recipes"},
 *     summary="Delete a recipe",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Deletion status")
 * )
 */
Flight::route('DELETE /recipes/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::recipeService()->delete($id));
});
