<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../dao/AuthDao.php';

use Firebase\JWT\JWT;

class AuthService extends BaseService {

    public function __construct() {
        $this->dao = new AuthDao();
    }

    public function register($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $existingUser = $this->dao->getUserByEmail($data['email']);
        if ($existingUser) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        // Always force role to 'user' for security (prevent privilege escalation)
        $data['role'] = 'user';

        $userId = $this->dao->insert($data);
        return ['success' => true, 'data' => ['id' => $userId]];
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->dao->getUserByEmail($data['email']);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        if (!password_verify($data['password'], $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        unset($user['password_hash']);

        $jwt_payload = [
            'user' => $user,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // valid for 1 day
        ];

        $token = JWT::encode($jwt_payload, Config::JWT_SECRET(), 'HS256');

        return ['success' => true, 'data' => array_merge($user, ['token' => $token])];
    }
}
