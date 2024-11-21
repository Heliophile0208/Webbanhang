<?php
// Kết nối cơ sở dữ liệu
include 'config/database.php';

// Lấy dữ liệu từ form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Truy vấn để thêm người dùng vào CSDL
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $message = "";
    // Kiểm tra nếu câu lệnh SQL được chuẩn bị thành công
    if ($stmt === false) {
        die('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }

    // Gắn giá trị vào câu lệnh SQL
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    
    // Thực thi câu lệnh SQL
    if ($stmt->execute()) {
        $message= "Đăng ký thành công!";
    } else {
        $message= "Có lỗi xảy ra khi đăng ký: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Đăng Ký</title>
</head>
<style>
   /* Đặt kiểu cho toàn bộ trang */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container bao quanh form */
        .container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
        }

        /* Form đăng nhập và đăng ký */
        .form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Đặt kiểu cho các input */
        input[type="text"], input[type="password"], select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        /* Cải thiện giao diện của các button */
        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Các liên kết */
        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Điều chỉnh độ rộng cho các form */
        h2 {
            text-align: center;
            color: #333;
        }

        /* Ẩn form đăng ký khi chưa cần thiết */
        .hidden {
            display: none;
        }

        /* Đặt chiều rộng tối đa cho form */
        .form-container {
            width: 100%;
        }

        /* Đảm bảo không gian giữa các form */
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Kiểu cho thông báo lỗi */
        .message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
  

</style>
<body>
<div class="container">
    <form action="register.php" method="POST">
        <h2>Đăng Ký</h2>
<?php if ($message != ""): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <select name="role" required>
            <option value="" disabled selected>Chọn vai trò</option>
            <option value="user">Người dùng</option>
            <option value="admin">Quản trị viên</option>
        </select>
        <button type="submit">Đăng Ký</button>
<p>Chưa có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    </form>
</div>
</body>
</html>