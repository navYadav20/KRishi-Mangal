<?php
require_once '../includes/db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['post_id'])) {
    header("Location: ?page=dashboard");
    exit();
}

$post_id = $_GET['post_id'];

// Verify the post belongs to the user
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post || $post['user_id'] != $user_id) {
    header("Location: ?page=dashboard");
    exit();
}

// Delete the post
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);

header("Location: ?page=dashboard");
exit();