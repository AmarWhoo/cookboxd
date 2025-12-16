<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../dao/UserDao.php';

class UserService extends BaseService {
    public function __construct() {
        $this->dao = new UserDao();
    }

    public function getByEmail($email) {
        return $this->dao->getByEmail($email);
    }

    public function getByUsername($username) {
        return $this->dao->getByUsername($username);
    }
}
