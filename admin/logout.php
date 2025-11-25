<?php
session_start();
// Xóa session admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Quay về trang login
header("Location: login.php");
?>
