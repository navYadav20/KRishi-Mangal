<?php
require_once '../includes/db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Define the available categories
$categories = ['Crop Farming', 'Livestock', 'Sustainable Practices', 'Others'];

// Handle new post submission
if(isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    
    // Validate category
    if (!in_array($category, $categories)) {
        $category = $categories[0]; // Default to the first category if invalid
    }
    
    // Handle file upload
    if(isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['media']['name'];
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if(in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = "../uploads/" . $new_file_name;

            if(move_uploaded_file($file_tmp, $upload_path)) {
                $content .= "\n<img src='../uploads/$new_file_name' alt='Uploaded media'>";
            } else {
                echo "Error: Failed to upload the file.";
            }
        } else {
            echo "Error: Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } elseif(isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
        echo "Error: File upload error (Code: " . $_FILES['media']['error'] . ")";
    }
    
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, category) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $content, $category]);
    header("Location: ?page=dashboard");
    exit();
}

// Fetch posts by the logged-in user only
$stmt = $conn->prepare("SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="user-info">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Account Created: <?php 
                $created_at = new DateTime($user['created_at'], new DateTimeZone('Asia/Kolkata'));
                echo $created_at->format('Y-m-d H:i:s');
            ?></p>
        </div>

        <h2>Create Post</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="content" placeholder="Content" required></textarea>
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="media" accept="image/*,video/*">
            <input type="submit" name="submit" value="Post">
        </form>
        
        <h2>Your Posts</h2>
        <?php if(empty($posts)): ?>
            <p>You haven't posted any blogs yet.</p>
        <?php else: ?>
            <?php foreach($posts as $post): ?>
                <div class="post">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <div class="post-meta">
                        Posted on <?php echo $post['created_at']; ?> | 
                        Category: <?php echo htmlspecialchars($post['category']); ?> | 
                        Views: <?php echo $post['views']; ?>
                        <?php
                        $post_time = new DateTime($post['created_at'], new DateTimeZone('Asia/Kolkata'));
                        $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
                        $interval = $post_time->diff($current_time);
                        $minutes_diff = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                        if($minutes_diff <= 10) {
                            echo ' | <a href="?page=edit_post&post_id=' . $post['id'] . '" class="edit-link">Edit</a>';
                        } else {
                            echo ' | <span class="edit-link disabled">Edit (Expired)</span>';
                        }
                        echo ' | <a href="?page=delete_post&post_id=' . $post['id'] . '" class="delete-link" onclick="return confirm(\'Are you sure you want to delete this post?\');">Delete</a>';
                        ?>
                    </div>
                    <div class="post-content">
                        <?php 
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
                        echo nl2br(htmlspecialchars(implode("\n", $text_content)));
                        ?>
                    </div>
                    <div class="post-images">
                        <?php 
                        foreach ($images as $image) {
                            echo $image;
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>