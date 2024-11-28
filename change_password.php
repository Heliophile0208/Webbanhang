<?php
session_start();
include_once 'config/database.php';

$message = "";

// Xử lý khi người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Kiểm tra tên đăng nhập có tồn tại hay không
    $userQuery = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = "Tên đăng nhập không tồn tại.";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } 
   else {
        // Lấy user_id từ kết quả truy vấn
        $user = $result->fetch_assoc();
        $userId = $user['id'];

        // Hash mật khẩu mới và cập nhật vào cơ sở dữ liệu
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $newHashedPassword, $userId);

        if ($stmt->execute()) {
            $message = "Đổi mật khẩu thành công.";
        } else {
            $message = "Lỗi khi đổi mật khẩu: " . $conn->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu</title>      
<link rel="stylesheet" href="../css/login_logout.css">
</head>
<body>
    <div class="container">
        <h2>Đổi Mật Khẩu</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'thành công') !== false ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required>

            <label for="new_password">Mật khẩu mới:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Xác nhận mật khẩu mới:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Đổi mật khẩu</button>
        </form>
<p class="text_dangki"> <a href="login.php">Đăng nhập ngay</a></p>

    </div>
</body>
</html>