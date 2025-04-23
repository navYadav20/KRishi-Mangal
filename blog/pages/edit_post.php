<?php
require_once '../includes/db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if post_id is provided
if(!isset($_GET['post_id'])) {
    header("Location: ?page=dashboard");
    exit();
}

$post_id = $_GET['post_id'];

// Fetch the post to ensure it exists and belongs to the user
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

if(!$post) {
    header("Location: ?page=dashboard");
    exit();
}

// Check if the post is still editable (within 10 minutes)
$post_time = new DateTime($post['created_at'], new DateTimeZone('Asia/Kolkata'));
$current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$interval = $post_time->diff($current_time);
$minutes_diff = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

if($minutes_diff > 10) {
    header("Location: ?page=dashboard");
    exit();
}

// Define the available categories
$categories = ['Crop Farming', 'Livestock', 'Sustainable Practices', 'Others'];

// Handle form submission for editing
if(isset($_POST['update'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];

    // Validate category
    if (!in_array($category, $categories)) {
        $category = $categories[0]; // Default to the first category if invalid
    }

    // Handle new file upload (if any)
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
    }

    // Update the post
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $content, $category, $post_id, $user_id]);

    header("Location: ?page=dashboard");
    exit();
}

// Separate text and images for display
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h2>Edit Post</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            <textarea name="content" required><?php echo htmlspecialchars(implode("\n", $text_content)); ?></textarea>
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo $post['category'] == $cat ? 'selected' : ''; ?>>
                        <?php echo $cat; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="post-images">
                <?php 
                if (!empty($images)) {
                    echo "<p>Existing Images:</p>";
                    foreach ($images as $image) {
                        echo $image;
                    }
                }
                ?>
            </div>
            <input type="file" name="media" accept="image/*,video/*">
            <input type="submit" name="update" value="Update Post">
        </form>
        <a href="?page=dashboard">Cancel</a>
    </div>
</body>
</html>