<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recipes_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Validate recipe ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<h1>Error: Invalid recipe request.</h1>");
}

$recipe_id = intval($_GET['id']);

// Fetch the recipe
$stmt = $conn->prepare(
    "SELECT id, title, ingredients, instructions, image_path 
     FROM recipes 
     WHERE id = ?"
);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$recipe = $result->fetch_assoc()) {
    die("<h1>Error: Recipe not found.</h1>");
}

// ---- Build keyword-based similar recipe search ---- //
$title_words = preg_split("/\s+/", strtolower($recipe['title']));

// Remove common filler words
$stopwords = ["the","and","with","of","for","a","to","on","in","at"];
$title_words = array_diff($title_words, $stopwords);

$similar_query = "";
$similar_types = "";
$similar_params = [];

foreach ($title_words as $word) {
    $similar_query .= " OR title LIKE ? ";
    $similar_types .= "s";
    $similar_params[] = "%$word%";
}

$similar_recipes = [];

if (!empty($similar_query)) {
    $sql = "SELECT id, title, image_path 
            FROM recipes 
            WHERE id != ? AND (" . substr($similar_query, 4) . ") 
            LIMIT 4";

    $similar_stmt = $conn->prepare($sql);

    // bind dynamic parameters
    $similar_types = "i" . $similar_types;
    $bind_values = array_merge([$recipe_id], $similar_params);

    $similar_stmt->bind_param($similar_types, ...$bind_values);
    $similar_stmt->execute();
    $similar_recipes = $similar_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['title']); ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.6;
        }
        img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        h1 { margin-bottom: 10px; }
        h2 { margin-top: 30px; }
        ul { margin-bottom: 20px; }
        .similar-container {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .similar-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 180px;
            padding: 10px;
            text-align: center;
        }
        .similar-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }
        .similar-card a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>

    <?php if (!empty($recipe['image_path'])): ?>
        <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="">
    <?php endif; ?>

    <!-- Ingredients -->
    <h2>Ingredients</h2>
    <ul>
        <?php 
            $ingredients = explode("*", $recipe['ingredients']);
            foreach ($ingredients as $ing) {
                echo "<li>" . htmlspecialchars(trim($ing)) . "</li>";
            }
        ?>
    </ul>

    <!-- Instructions / Steps -->
    <h2>Instructions</h2>
    <ol>
        <?php 
            $steps = explode("*", $recipe['instructions']);
            foreach ($steps as $step) {
                echo "<li>" . htmlspecialchars(trim($step)) . "</li>";
            }
        ?>
    </ol>

    <!-- Similar Recipes -->
    <h2>Similar Recipes</h2>

    <?php if (!empty($similar_recipes)): ?>
        <div class="similar-container">
            <?php foreach ($similar_recipes as $s): ?>
                <div class="similar-card">
                    <?php if (!empty($s['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($s['image_path']); ?>" alt="">
                    <?php else: ?>
                        <img src="placeholder.jpg" alt="">
                    <?php endif; ?>

                    <a href="single_recipe.php?id=<?php echo $s['id']; ?>">
                        <?php echo htmlspecialchars($s['title']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No similar recipes found.</p>
    <?php endif; ?>

</body>
</html>
<?php

$stmt->close();
$conn->close();
?>
