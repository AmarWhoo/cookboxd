<?php

class AuthMiddleware {

    public function authorizeRole($requiredRole) {
        $user = Flight::get('user');
        if (!$user) {
            Flight::halt(401, 'Unauthorized: user not authenticated');
        }
        if ($user->role !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
    }

    public function authorizeRoles($roles) {
        $user = Flight::get('user');
        if (!$user) {
            Flight::halt(401, 'Unauthorized: user not authenticated');
        }
        if (!in_array($user->role, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
    }
}
