<?php  
session_start();
include_once 'config/database.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn CSDL để kiểm tra thông tin người dùng
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    // Kiểm tra lỗi khi chuẩn bị câu lệnh SQL
    if ($stmt === false) {
        die('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }

    // Gắn giá trị vào câu lệnh SQL
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra nếu có người dùng với tên đăng nhập tương ứng
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu có khớp không
        if (password_verify($password, $user['password'])) {
            // Lấy vai trò của người dùng
            $role = $user['role'];

            // Lưu tên người dùng và vai trò vào session
            $_SESSION['username'] = $username;
                $_SESSION['role'] =$role;

            // Điều hướng người dùng đến trang tương ứng theo vai trò
            if ($role === 'admin') {
                header("Location: admin/index.php");
                exit;
            } elseif ($role === 'user') {
                header("Location: user/index.php");
                exit;
            } else {
                $message = "Vai trò không hợp lệ!";
            }
        } else {
            $message = "Mật khẩu không đúng!";
        }
    } else {
        $message = "Tên đăng nhập không tồn tại!";
    }
} else {
    $message = "Vui lòng nhập tên đăng nhập và mật khẩu!";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Đăng Nhập</title>

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

</head>
<body>
    <!-- Form đăng nhập -->
    <div class="container">
        <form action="login.php" method="post">
            <h2>Đăng Nhập</h2>

            <!-- Hiển thị thông báo nếu có -->
            <?php if ($message != ""): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng Nhập</button>
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </form>

    </div>
</body>
</html>