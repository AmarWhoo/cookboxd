<?php

/**
 * User Routes - REST API endpoints for user management
 */

$userService = new UserService();

/**
 * @OA\Post(
 *     path="/api/users/register",
 *     tags={"users"},
 *     summary="Register a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "email", "password"},
 *             @OA\Property(property="username", type="string", example="johndoe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="Password123"),
 *             @OA\Property(property="role", type="string", example="user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('POST /api/users/register', function() use ($userService) {
    $data = Flight::getJsonInput();
    $result = $userService->registerUser($data);
    
    $statusCode = $result['success'] ? 201 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Post(
 *     path="/api/users/login",
 *     tags={"users"},
 *     summary="User login",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"login", "password"},
 *             @OA\Property(property="login", type="string", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="Password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */
Flight::route('POST /api/users/login', function() use ($userService) {
    $data = Flight::getJsonInput();
    
    $login = $data['login'] ?? '';
    $password = $data['password'] ?? '';
    
    $result = $userService->loginUser($login, $password);
    
    $statusCode = $result['success'] ? 200 : 401;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Get(
 *     path="/api/users",
 *     tags={"users"},
 *     summary="Get all users",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all users"
 *     )
 * )
 */
Flight::route('GET /api/users', function() use ($userService) {
    $users = $userService->getAllUsers();
    
    Flight::json([
        'success' => true,
        'data' => $users
    ]);
});

/**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User details"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('GET /api/users/@id', function($id) use ($userService) {
    $user = $userService->getUserById($id);
    
    if ($user) {
        Flight::json([
            'success' => true,
            'data' => $user
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
});

/**
 * @OA\Put(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Update user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="username", type="string", example="johndoe_updated"),
 *             @OA\Property(property="email", type="string", example="john.new@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error"
 *     )
 * )
 */
Flight::route('PUT /api/users/@id', function($id) use ($userService) {
    $data = Flight::getJsonInput();
    $result = $userService->updateUser($id, $data);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Post(
 *     path="/api/users/{id}/password",
 *     tags={"users"},
 *     summary="Change user password",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"current_password", "new_password"},
 *             @OA\Property(property="current_password", type="string", example="OldPassword123"),
 *             @OA\Property(property="new_password", type="string", example="NewPassword456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password changed successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error or incorrect current password"
 *     )
 * )
 */
Flight::route('POST /api/users/@id/password', function($id) use ($userService) {
    $data = Flight::getJsonInput();
    
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    
    $result = $userService->changePassword($id, $currentPassword, $newPassword);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});

/**
 * @OA\Delete(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Delete user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('DELETE /api/users/@id', function($id) use ($userService) {
    $result = $userService->deleteUser($id);
    
    $statusCode = $result['success'] ? 200 : 400;
    Flight::json($result, $statusCode);
});
