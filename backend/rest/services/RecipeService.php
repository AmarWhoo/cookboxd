<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../dao/RecipeDao.php';

class RecipeService extends BaseService {
    public function __construct() {
        $this->dao = new RecipeDao();
    }

    public function getByUserId($userId) {
        return $this->dao->getByUserId($userId);
    }

    public function getByCategoryId($categoryId) {
        return $this->dao->getByCategoryId($categoryId);
    }
}
