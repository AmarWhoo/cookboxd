<?php

/**
 * @OA\Get(
 *     path="/ingredients",
 *     tags={"ingredients"},
 *     summary="Get all ingredients",
 *     @OA\Response(response=200, description="Array of ingredients")
 * )
 */
Flight::route('GET /ingredients', function() {
    Flight::json(Flight::ingredientService()->getAll());
});

/**
 * @OA\Get(
 *     path="/ingredients/{id}",
 *     tags={"ingredients"},
 *     summary="Get ingredient by ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Ingredient object")
 * )
 */
Flight::route('GET /ingredients/@id', function($id) {
    Flight::json(Flight::ingredientService()->getById($id));
});

/**
 * @OA\Get(
 *     path="/ingredients/recipe/{recipeId}",
 *     tags={"ingredients"},
 *     summary="Get ingredients by recipe ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="recipeId", required=true),
 *     @OA\Response(response=200, description="Array of ingredients")
 * )
 */
Flight::route('GET /ingredients/recipe/@recipeId', function($recipeId) {
    Flight::json(Flight::ingredientService()->getByRecipeId($recipeId));
});

/**
 * @OA\Post(
 *     path="/ingredients",
 *     tags={"ingredients"},
 *     summary="Create a new ingredient",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="recipe_id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="quantity", type="string")
 *     )),
 *     @OA\Response(response=200, description="Created ingredient ID")
 * )
 */
Flight::route('POST /ingredients', function() {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::USER]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::ingredientService()->add($data));
});

/**
 * @OA\Put(
 *     path="/ingredients/{id}",
 *     tags={"ingredients"},
 *     summary="Update an ingredient",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="quantity", type="string")
 *     )),
 *     @OA\Response(response=200, description="Updated ingredient")
 * )
 */
Flight::route('PUT /ingredients/@id', function($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::USER]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::ingredientService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/ingredients/{id}",
 *     tags={"ingredients"},
 *     summary="Delete an ingredient",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Deletion status")
 * )
 */
Flight::route('DELETE /ingredients/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::ingredientService()->delete($id));
});
