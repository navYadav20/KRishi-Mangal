<?php
require_once '../includes/db_connect.php';

// Fetch all unique writers (names) who have written posts
$writers_stmt = $conn->query("SELECT DISTINCT users.name, users.id FROM users JOIN posts ON users.id = posts.user_id ORDER BY users.name");
$writers = $writers_stmt->fetchAll();

// Fetch all unique categories
$categories_stmt = $conn->query("SELECT DISTINCT category FROM posts ORDER BY category");
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get the selected writer and category from the GET parameters (if any)
$selected_writer_id = isset($_GET['writer']) ? (int)$_GET['writer'] : 0;
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// Build the query based on the selected filters
$query = "SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.id";
$conditions = [];
$params = [];

if ($selected_writer_id > 0) {
    $conditions[] = "posts.user_id = ?";
    $params[] = $selected_writer_id;
}

if (!empty($selected_category) && in_array($selected_category, $categories)) {
    $conditions[] = "posts.category = ?";
    $params[] = $selected_category;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Public Blog Posts</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Blog Posts</h1>
        <p>Welcome to our blog! Browse all posts below:</p>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
            <input type="hidden" name="page" value="view_blogs">
            
            <label for="writer">Filter by Writer: </label>
            <select name="writer" id="writer" onchange="this.form.submit()">
                <option value="0" <?php echo $selected_writer_id == 0 ? 'selected' : ''; ?>>All Writers</option>
                <?php foreach ($writers as $writer): ?>
                    <option value="<?php echo $writer['id']; ?>" <?php echo $selected_writer_id == $writer['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($writer['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="category">Filter by Category: </label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="" <?php echo empty($selected_category) ? 'selected' : ''; ?>>All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo $selected_category == $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if(empty($posts)): ?>
            <p>No posts available yet.</p>
        <?php else: ?>
            <?php foreach($posts as $post): ?>
                <div class="post">
                    <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <div class="post-meta">
                        <?php
                        $post_time = new DateTime($post['created_at'], new DateTimeZone('Asia/Kolkata'));
                        $formatted_time = $post_time->format('Y-m-d H:i:s');
                        ?>
                        Posted by <?php echo htmlspecialchars($post['name']); ?> 
                        on <?php echo $formatted_time; ?> | 
                        Category: <?php echo htmlspecialchars($post['category']); ?> | 
                        Views: <?php echo $post['views']; ?>
                    </div>
                    <div class="post-content">
                        <?php 
                        // Separate text and images
                        $lines = explode("\n", $post['content']);
                        $text_content = [];
                        $images = [];

                        foreach ($lines as $line) {
                            if (strpos($line, '<img') !== false) {
                                $images[] = $line;
                            } else {
                                $text_content[] = $line;
                            }
                        }

                        // Combine text content and get a preview (first 100 characters)
                        $full_text = implode("\n", $text_content);
                        $preview_length = 100;
                        $preview = strlen($full_text) > $preview_length 
                            ? substr($full_text, 0, $preview_length) . '...' 
                            : $full_text;
                        echo nl2br(htmlspecialchars($preview));
                        ?>
                    </div>
                    <a href="?page=view_post&post_id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <a href="?page=home" class="back-link">Back to Home</a>
    </div>
</body>
</html>