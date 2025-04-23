<?php
session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$allowed_pages = ['home', 'login', 'register', 'dashboard', 'view_blogs', 'view_post', 'edit_post', 'delete_post', 'logout'];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

require_once "pages/$page.php";