<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recipes_db";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all recipes
$result = $conn->query("SELECT id, title, image_path, ingredients FROM recipes ORDER BY title ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>All Recipes | Healthy Eats</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <header class="header">
    <div class="logo">🥗 Healthy Eats</div>
    <nav class="nav">
      <a href="index.html">Home</a>
      <a href="recipes.php">All Recipes</a>
    </nav>
    <form action="no_results.html" class="search-form">
      <input type="text" name="q" placeholder="Search recipes..." class="search-bar">
      <button type="submit" class="search-btn">🔍</button>
    </form>
  </header>

  <main class="main">
    <section class="recipes-section">
      <h1>All Recipes</h1>
      
      <div class="recipe-grid">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($recipe = $result->fetch_assoc()): ?>
            <?php 
              $recipe_id = $recipe['id'];
              // Remove "Recipe_" prefix from title
              $clean_title = preg_replace('/^Recipe_/i', '', $recipe['title']);
              $image = !empty($recipe['image_path']) ? $recipe['image_path'] : 'default_recipe.jpg';
              // Optionally, you could show first few ingredients in description
              $ingredients_preview = implode(", ", array_slice(explode("*", $recipe['ingredients']), 0, 3));
            ?>
            <a href="recipe.php?id=<?php echo $recipe_id; ?>" class="recipe-card">
              <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($clean_title); ?>" />
              <h3><?php echo htmlspecialchars($clean_title); ?></h3>
              <p><?php echo htmlspecialchars($ingredients_preview); ?>...</p>
            </a>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No recipes found.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-top">
      <div class="footer-brand">
        <h3>Healthy Eats</h3>
        <p>Your destination for delicious, modern, and nutritious recipes from around the world.</p>
      </div>
      <div class="footer-links">
        <div>
          <h4>Quick Links</h4>
          <a href="index.html">Home</a>
          <a href="recipes.php">All Recipes</a>
        </div>
        <div>
          <h4>Information</h4>
          <a href="#">About Us</a>
          <a href="#">Contact</a>
        </div>
        <div>
          <h4>Follow Us</h4>
          <a href="#">Instagram</a>
          <a href="#">Facebook</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      © 2025 HealthyEats. All rights reserved.
    </div>
  </footer>
</body>
</html>

<?php
$conn->close();
?>
