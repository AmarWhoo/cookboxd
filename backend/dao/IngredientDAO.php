<?php
require_once 'BaseDao.php';

class IngredientDao extends BaseDao {
    public function __construct() {
        parent::__construct("ingredients");
    }

    public function getByRecipeId($recipe_id) {
        $stmt = $this->connection->prepare("SELECT * FROM ingredients WHERE recipe_id = :recipe_id");
        $stmt->bindParam(':recipe_id', $recipe_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>