<?php
include '../../../config/database.php';

// Lấy từ query string
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Kiểm tra và tìm kiếm người dùng có tên chứa từ khóa
$query = "SELECT id, username FROM users WHERE username LIKE ? LIMIT 10";  // Giới hạn 10 kết quả
$stmt = $conn->prepare($query);
$searchTerm = "%" . $searchTerm . "%";  // Tìm kiếm có chứa từ khóa
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}

// Trả về kết quả tìm kiếm dưới dạng JSON
echo json_encode(['results' => $users]);
?>