<?php

/**
 * @OA\Get(
 *     path="/categories",
 *     tags={"categories"},
 *     summary="Get all categories",
 *     @OA\Response(response=200, description="Array of categories")
 * )
 */
Flight::route('GET /categories', function() {
    Flight::json(Flight::categoryService()->getAll());
});

/**
 * @OA\Get(
 *     path="/categories/{id}",
 *     tags={"categories"},
 *     summary="Get category by ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Category object")
 * )
 */
Flight::route('GET /categories/@id', function($id) {
    Flight::json(Flight::categoryService()->getById($id));
});

/**
 * @OA\Post(
 *     path="/categories",
 *     tags={"categories"},
 *     summary="Create a new category",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="name", type="string")
 *     )),
 *     @OA\Response(response=200, description="Created category ID")
 * )
 */
Flight::route('POST /categories', function() {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::categoryService()->add($data));
});

/**
 * @OA\Put(
 *     path="/categories/{id}",
 *     tags={"categories"},
 *     summary="Update a category",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="name", type="string")
 *     )),
 *     @OA\Response(response=200, description="Updated category")
 * )
 */
Flight::route('PUT /categories/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::categoryService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/categories/{id}",
 *     tags={"categories"},
 *     summary="Delete a category",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Deletion status")
 * )
 */
Flight::route('DELETE /categories/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::categoryService()->delete($id));
});
