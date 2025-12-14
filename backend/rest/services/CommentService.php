<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../dao/CommentDao.php';

class CommentService extends BaseService {
    public function __construct() {
        $this->dao = new CommentDao();
    }

    public function getByRecipeId($recipeId) {
        return $this->dao->getByRecipeId($recipeId);
    }

    public function getByUserId($userId) {
        return $this->dao->getByUserId($userId);
    }
}
