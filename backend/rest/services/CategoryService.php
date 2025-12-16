<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../dao/CategoryDao.php';

class CategoryService extends BaseService {
    public function __construct() {
        $this->dao = new CategoryDao();
    }

    public function getByName($name) {
        return $this->dao->getByName($name);
    }
}
