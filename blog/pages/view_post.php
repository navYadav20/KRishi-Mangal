<?php
require_once '../includes/db_connect.php';

// Check if post_id is provided
if (!isset($_GET['post_id'])) {
    header("Location: ?page=view_blogs");
    exit();
}

$post_id = $_GET['post_id'];

// Fetch the specific post
$stmt = $conn->prepare("SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: ?page=view_blogs");
    exit();
}

// Increment views for this post only if the user hasn't viewed it in this session
if (!isset($_SESSION['viewed_posts'])) {
    $_SESSION['viewed_posts'] = [];
}

if (!in_array($post_id, $_SESSION['viewed_posts'])) {
    $stmt = $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    $_SESSION['viewed_posts'][] = $post_id;
    
    // Refresh the post data to get the updated view count
    $stmt = $conn->prepare("SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog Post</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="post">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
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

                // Display full text content
                echo nl2br(htmlspecialchars(implode("\n", $text_content)));
                ?>
            </div>
            <div class="post-images">
                <?php 
                // Display images below the text
                foreach ($images as $image) {
                    echo $image;
                }
                ?>
            </div>
        </div>
        <a href="?page=view_blogs" class="back-link">Back to All Posts</a>
    </div>
</body>
</html>