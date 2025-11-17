# Cookboxd REST API Documentation

## Base URL
```
http://localhost/AmarCajdric/cookboxd/backend
```

## Response Format
All responses are in JSON format with the following structure:
```json
{
  "success": true/false,
  "message": "Description of result",
  "data": {} // Optional data payload
}
```

## HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation error)
- `401` - Unauthorized
- `404` - Not Found
- `500` - Server Error

---

## User Endpoints

### Register User
**POST** `/api/users/register`
```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "Password123",
  "role": "user"
}
```

### Login User
**POST** `/api/users/login`
```json
{
  "login": "john@example.com",
  "password": "Password123"
}
```

### Get All Users
**GET** `/api/users`

### Get User by ID
**GET** `/api/users/{id}`

### Update User
**PUT** `/api/users/{id}`
```json
{
  "username": "johndoe_updated",
  "email": "john.new@example.com"
}
```

### Change Password
**POST** `/api/users/{id}/password`
```json
{
  "current_password": "OldPassword123",
  "new_password": "NewPassword456"
}
```

### Delete User
**DELETE** `/api/users/{id}`

---

## Category Endpoints

### Create Category
**POST** `/api/categories`
```json
{
  "name": "Desserts"
}
```

### Get All Categories
**GET** `/api/categories`

### Get Category by ID
**GET** `/api/categories/{id}`

### Get Recipe Count
**GET** `/api/categories/{id}/count`

### Update Category
**PUT** `/api/categories/{id}`
```json
{
  "name": "Sweet Desserts"
}
```

### Delete Category
**DELETE** `/api/categories/{id}`

---

## Recipe Endpoints

### Create Recipe
**POST** `/api/recipes`
```json
{
  "user_id": 1,
  "category_id": 2,
  "title": "Chocolate Cake",
  "description": "Delicious chocolate cake recipe",
  "image_url": "https://example.com/image.jpg"
}
```

### Get All Recipes
**GET** `/api/recipes`

### Get Paginated Recipes
**GET** `/api/recipes?page=1&per_page=10`

### Search Recipes
**GET** `/api/recipes/search?q=chocolate`

### Get Recipes by User
**GET** `/api/recipes/user/{userId}`

### Get Recipes by Category
**GET** `/api/recipes/category/{categoryId}`

### Get Recipe by ID
**GET** `/api/recipes/{id}`

### Update Recipe
**PUT** `/api/recipes/{id}`
```json
{
  "user_id": 1,
  "title": "Updated Chocolate Cake",
  "description": "Even better chocolate cake",
  "category_id": 2
}
```

### Delete Recipe
**DELETE** `/api/recipes/{id}`
```json
{
  "user_id": 1
}
```

---

## Ingredient Endpoints

### Create Ingredient
**POST** `/api/ingredients`
```json
{
  "recipe_id": 1,
  "name": "Flour",
  "quantity": "2 cups"
}
```

### Create Multiple Ingredients
**POST** `/api/ingredients/batch`
```json
{
  "recipe_id": 1,
  "ingredients": [
    {"name": "Flour", "quantity": "2 cups"},
    {"name": "Sugar", "quantity": "1 cup"},
    {"name": "Eggs", "quantity": "3"}
  ]
}
```

### Get All Ingredients
**GET** `/api/ingredients`

### Get Ingredient by ID
**GET** `/api/ingredients/{id}`

### Get Ingredients by Recipe
**GET** `/api/ingredients/recipe/{recipeId}`

### Get Ingredient Count
**GET** `/api/ingredients/recipe/{recipeId}/count`

### Update Ingredient
**PUT** `/api/ingredients/{id}`
```json
{
  "name": "All-Purpose Flour",
  "quantity": "2.5 cups"
}
```

### Replace All Recipe Ingredients
**PUT** `/api/ingredients/recipe/{recipeId}/replace`
```json
{
  "ingredients": [
    {"name": "Flour", "quantity": "3 cups"},
    {"name": "Sugar", "quantity": "2 cups"}
  ]
}
```

### Delete Ingredient
**DELETE** `/api/ingredients/{id}`

### Delete All Recipe Ingredients
**DELETE** `/api/ingredients/recipe/{recipeId}`

---

## Comment Endpoints

### Create Comment
**POST** `/api/comments`
```json
{
  "recipe_id": 1,
  "user_id": 2,
  "content": "Great recipe! Loved it."
}
```

### Get All Comments
**GET** `/api/comments`

### Get Paginated Comments
**GET** `/api/comments?page=1&per_page=20`

### Get Comment by ID
**GET** `/api/comments/{id}`

### Get Comments by Recipe
**GET** `/api/comments/recipe/{recipeId}`

### Get Comment Count for Recipe
**GET** `/api/comments/recipe/{recipeId}/count`

### Get Comments by User
**GET** `/api/comments/user/{userId}`

### Update Comment
**PUT** `/api/comments/{id}`
```json
{
  "user_id": 2,
  "content": "Updated comment text"
}
```

### Delete Comment
**DELETE** `/api/comments/{id}`
```json
{
  "user_id": 2,
  "is_admin": false
}
```

### Delete All Recipe Comments (Admin)
**DELETE** `/api/comments/recipe/{recipeId}`

### Delete All User Comments (Admin)
**DELETE** `/api/comments/user/{userId}`

---

## Validation Rules

### User
- **username**: 3-50 characters, alphanumeric + underscore
- **email**: Valid email format
- **password**: 8+ characters, must contain letter and number
- **role**: Either "user" or "admin"

### Category
- **name**: 2-100 characters, alphanumeric + spaces + hyphens

### Recipe
- **title**: 3-255 characters
- **description**: 10+ characters
- **image_url**: Valid URL format, max 500 characters

### Ingredient
- **name**: Required, max 255 characters
- **quantity**: Required, max 100 characters

### Comment
- **content**: 3-2000 characters

---

## Testing the API

### Using curl:
```bash
# Register a user
curl -X POST http://localhost/AmarCajdric/cookboxd/backend/api/users/register \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@example.com","password":"Test1234"}'

# Get all recipes
curl http://localhost/AmarCajdric/cookboxd/backend/api/recipes

# Create a category
curl -X POST http://localhost/AmarCajdric/cookboxd/backend/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"Desserts"}'
```

### Using Postman:
1. Import this documentation as a collection
2. Set base URL as environment variable
3. Test each endpoint with sample data

---

## Error Handling

All errors return appropriate HTTP status codes with error messages:

```json
{
  "success": false,
  "message": "Detailed error message"
}
```

Common error scenarios:
- Missing required fields
- Invalid data format
- Duplicate entries (email, username, category name)
- Record not found
- Permission denied (ownership checks)
- Database connection errors
