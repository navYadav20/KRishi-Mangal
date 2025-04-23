<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<div class="navbar">
    <div class="navbar-left">
        <a href="?page=home" class="<?php echo $current_page == 'home' ? 'active' : ''; ?>">Home</a>
        <a href="?page=view_blogs" class="<?php echo $current_page == 'view_blogs' ? 'active' : ''; ?>">View Blogs</a>
    </div>
    <div class="navbar-right">
        <?php if ($current_page == 'view_blogs' || $current_page == 'view_post'): ?>
            <!-- Language Selector -->
            <div class="language-selector">
                <div id="google_translate_element"></div>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="?page=dashboard" class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="?page=logout" class="<?php echo $current_page == 'logout' ? 'active' : ''; ?>">Logout</a>
        <?php else: ?>
            <a href="?page=login" class="<?php echo $current_page == 'login' ? 'active' : ''; ?>">Login</a>
            <a href="?page=register" class="<?php echo $current_page == 'register' ? 'active' : ''; ?>">Register</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($current_page == 'view_blogs' || $current_page == 'view_post'): ?>
    <!-- Google Translate Script -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en', // Default language of the page (English)
                includedLanguages: 'en,es,fr,hi,zh-CN,ar,de,ja,ko,pt,ru', // Languages to include in the dropdown
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE, // Simple dropdown layout
                autoDisplay: false // Prevent automatic translation on page load
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<?php endif; ?>