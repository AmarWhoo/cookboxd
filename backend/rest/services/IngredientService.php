<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../dao/IngredientDao.php';

class IngredientService extends BaseService {
    public function __construct() {
        $this->dao = new IngredientDao();
    }

    public function getByRecipeId($recipeId) {
        return $this->dao->getByRecipeId($recipeId);
    }
}
