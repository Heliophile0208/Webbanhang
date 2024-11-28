<?php
session_start();
include_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $UserID = $_POST['UserID'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Kiểm tra xem UserID có tồn tại trong cơ sở dữ liệu
    $checkQuery = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu có mật khẩu mới, hash và cập nhật
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssi", $username, $hashedPassword, $role, $UserID);
        } else {
            // Nếu không có mật khẩu mới, chỉ cập nhật username và role
            $updateQuery = "UPDATE users SET username = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssi", $username, $role, $UserID);
        }

        if ($stmt->execute()) {
            // Trả về thông báo thành công dưới dạng text
            echo "success";
        } else {
            echo "Lỗi khi cập nhật: " . $conn->error;
        }
    } else {
        echo "Người dùng không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>