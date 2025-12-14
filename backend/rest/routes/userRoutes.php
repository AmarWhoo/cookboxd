<?php

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"users"},
 *     summary="Get all users",
 *     @OA\Response(response=200, description="Array of users")
 * )
 */
Flight::route('GET /users', function() {
    Flight::json(Flight::userService()->getAll());
});

/**
 * @OA\Get(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="User object")
 * )
 */
Flight::route('GET /users/@id', function($id) {
    Flight::json(Flight::userService()->getById($id));
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"users"},
 *     summary="Create a new user",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="username", type="string"),
 *         @OA\Property(property="email", type="string"),
 *         @OA\Property(property="password_hash", type="string")
 *     )),
 *     @OA\Response(response=200, description="Created user ID")
 * )
 */
Flight::route('POST /users', function() {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::userService()->add($data));
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Update a user",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         @OA\Property(property="username", type="string"),
 *         @OA\Property(property="email", type="string")
 *     )),
 *     @OA\Response(response=200, description="Updated user")
 * )
 */
Flight::route('PUT /users/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::userService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Delete a user",
 *     @OA\Parameter(@OA\Schema(type="integer"), in="path", name="id", required=true),
 *     @OA\Response(response=200, description="Deletion status")
 * )
 */
Flight::route('DELETE /users/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::userService()->delete($id));
});
