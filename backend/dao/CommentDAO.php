<?php
require_once 'BaseDao.php';

class CommentDao extends BaseDao {
    public function __construct() {
        parent::__construct("comments");
    }

    public function getByRecipeId($recipe_id) {
        $stmt = $this->connection->prepare("SELECT * FROM comments WHERE recipe_id = :recipe_id");
        $stmt->bindParam(':recipe_id', $recipe_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserId($user_id) {
        $stmt = $this->connection->prepare("SELECT * FROM comments WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>