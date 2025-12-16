<?php

/**
 * @OA\Post(
 *     path="/auth/register",
 *     tags={"auth"},
 *     summary="Register a new user",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"email", "password", "username"},
 *         @OA\Property(property="username", type="string", example="johndoe"),
 *         @OA\Property(property="email", type="string", example="john@example.com"),
 *         @OA\Property(property="password", type="string", example="password123")
 *     )),
 *     @OA\Response(response=200, description="User registered successfully"),
 *     @OA\Response(response=500, description="Registration failed")
 * )
 */
Flight::route('POST /auth/register', function() {
    $data = Flight::request()->data->getData();
    $response = Flight::auth_service()->register($data);

    if ($response['success']) {
        Flight::json(['message' => 'User registered successfully', 'data' => $response['data']]);
    } else {
        Flight::halt(400, $response['error']);
    }
});

/**
 * @OA\Post(
 *     path="/auth/login",
 *     tags={"auth"},
 *     summary="Login and get JWT token",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"email", "password"},
 *         @OA\Property(property="email", type="string", example="john@example.com"),
 *         @OA\Property(property="password", type="string", example="password123")
 *     )),
 *     @OA\Response(response=200, description="Login successful, returns JWT token"),
 *     @OA\Response(response=500, description="Invalid credentials")
 * )
 */
Flight::route('POST /auth/login', function() {
    $data = Flight::request()->data->getData();
    $response = Flight::auth_service()->login($data);

    if ($response['success']) {
        Flight::json(['message' => 'Login successful', 'data' => $response['data']]);
    } else {
        Flight::halt(401, $response['error']);
    }
});
