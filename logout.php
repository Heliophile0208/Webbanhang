<?php
session_start(); // Khởi tạo session

// Xóa tất cả các biến trong session


// Hủy session
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập hoặc trang chủ
header("Location: ../login.php"); // Hoặc "Location: index.php" nếu bạn muốn chuyển về trang chủ
exit;
?>