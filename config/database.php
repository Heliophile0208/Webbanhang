<?php
/**
 * database.php - Kết nối MySQL linh hoạt cho Local và Render
 */

// Kiểm tra môi trường
$isRender = getenv("DB_HOST") ? true : false;

// Lấy thông tin kết nối
$servername = $isRender ? getenv("DB_HOST") : "localhost";
$username   = $isRender ? getenv("DB_USER") : "root";
$password   = $isRender ? getenv("DB_PASS") : "";
$dbname     = $isRender ? getenv("DB_NAME") : "shop_db";
$port       = $isRender ? getenv("DB_PORT") : 3306; // local vẫn dùng 3306

// Nếu Render cung cấp port là string, convert sang int
$port = (int)$port;

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// echo "Kết nối MySQL thành công!";
?>
