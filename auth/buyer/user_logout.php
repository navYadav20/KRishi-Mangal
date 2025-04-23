<?php
session_start();
unset($_SESSION['user_id']);
unset($_SESSION['buyer_unique_id']);
session_destroy();
header("Location: ../../index.php");
exit();
?>